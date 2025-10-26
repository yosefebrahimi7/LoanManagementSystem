<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanSchedule;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Services\ZarinpalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\LoanException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private ZarinpalService $zarinpalService
    ) {}

    /**
     * Initiate payment for a loan installment
     */
    public function initiatePayment(Loan $loan, int $scheduleId, ?int $amount = null): array
    {
        $schedule = LoanSchedule::where('id', $scheduleId)
            ->where('loan_id', $loan->id)
            ->firstOrFail();

        // Check if installment is already paid
        if ($schedule->status === LoanSchedule::STATUS_PAID) {
            throw LoanException::badRequest('این قسط قبلاً پرداخت شده است');
        }

        $amount = $amount ?? $schedule->remaining_amount;
        
        if ($amount <= 0) {
            throw LoanException::badRequest('مبلغ نامعتبر است');
        }

        // Create payment record using Repository
        $payment = $this->paymentRepository->create([
            'user_id' => auth()->id(),
            'loan_id' => $loan->id,
            'loan_schedule_id' => $schedule->id,
            'amount' => $amount,
            'payment_method' => LoanPayment::METHOD_ZARINPAL,
            'status' => LoanPayment::STATUS_PENDING,
        ]);

        // Request payment from Zarinpal
        $description = "پرداخت قسط {$schedule->installment_number} وام {$loan->id}";
        
        $result = $this->zarinpalService->requestPayment(
            amount: $amount,
            description: $description,
            callbackUrl: config('services.zarinpal.callback_url'),
            metadata: [
                'loan_id' => $loan->id,
                'schedule_id' => $schedule->id,
                'payment_id' => $payment->id,
            ]
        );

        if (!$result['success']) {
            throw LoanException::badRequest('امکان اتصال به درگاه پرداخت وجود ندارد');
        }

        // Update payment with gateway reference
        $this->paymentRepository->update($payment->id, [
            'gateway_reference' => $result['authority'],
            'status' => LoanPayment::STATUS_PENDING,
        ]);

        return [
            'success' => true,
            'payment_url' => $result['payment_url'],
            'authority' => $result['authority'],
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Process payment callback from gateway
     */
    public function processPaymentCallback(Request $request): mixed
    {
        try {
            $authority = $request->get('Authority');
            $status = $request->get('Status');

            Log::info('Payment callback received', [
                'authority' => $authority,
                'status' => $status,
            ]);

            if (!$authority) {
                return redirect(config('app.frontend_url', 'http://localhost:5174') . '/payment/failed?error=' . urlencode('Authority not provided'));
            }

            // Find payment by authority using Repository
            $payment = $this->paymentRepository->findByGatewayReference($authority);

            if (!$payment) {
                Log::error('Payment not found', ['authority' => $authority]);
                return redirect(config('app.frontend_url', 'http://localhost:5174') . '/payment/failed?error=' . urlencode('پرداخت یافت نشد'));
            }

            // If payment is already completed, return success
            if ($payment->status === LoanPayment::STATUS_COMPLETED) {
                return redirect(config('app.frontend_url', 'http://localhost:5174') . '/payment/success?payment_id=' . $payment->id);
            }

            if ($status === 'OK') {
                // Verify payment
                $verifyResult = $this->verifyPayment($payment);

                if ($verifyResult['success']) {
                    $this->completePayment($payment, $verifyResult);
                    
                    $successUrl = config('app.frontend_url', 'http://localhost:5174') . '/payment/success?payment_id=' . $payment->id;
                    return redirect($successUrl);
                }
                
                // Code 101 means payment already verified - treat as success
                if (isset($verifyResult['code']) && $verifyResult['code'] == 101) {
                    Log::info('Payment already verified (code 101)', ['payment_id' => $payment->id]);
                    $this->completePayment($payment, $verifyResult);
                    
                    $successUrl = config('app.frontend_url', 'http://localhost:5174') . '/payment/success?payment_id=' . $payment->id;
                    return redirect($successUrl);
                }

                // Payment verification failed
                $this->failPayment($payment, 'Verification failed: ' . ($verifyResult['message'] ?? 'Unknown error'));

                Log::error('Payment verification failed', [
                    'payment_id' => $payment->id,
                    'error' => $verifyResult['message'] ?? 'Unknown error',
                ]);

                $failedUrl = config('app.frontend_url', 'http://localhost:5174') . '/payment/failed?payment_id=' . $payment->id;
                return redirect($failedUrl);
            }

            // Status is not OK - user cancelled
            $this->failPayment($payment, 'User cancelled or payment failed');

            Log::warning('Payment not OK', [
                'payment_id' => $payment->id,
                'status' => $status,
            ]);

            $failedUrl = config('app.frontend_url', 'http://localhost:5174') . '/payment/failed?payment_id=' . $payment->id;
            return redirect($failedUrl);

        } catch (\Exception $e) {
            Log::error('Payment callback failed', [
                'error' => $e->getMessage(),
                'authority' => $request->get('Authority'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در پردازش بازگشت از درگاه',
            ], 500);
        }
    }

    /**
     * Verify payment with Zarinpal
     */
    public function verifyPayment(LoanPayment $payment): array
    {
        return $this->zarinpalService->verifyPayment($payment->gateway_reference, $payment->amount);
    }

    /**
     * Complete payment and update related records
     */
    public function completePayment(LoanPayment $payment, array $verifyResult): void
    {
        DB::beginTransaction();
        try {
            // Update payment using Repository
            $this->paymentRepository->update($payment->id, [
                'status' => LoanPayment::STATUS_COMPLETED,
                'gateway_response' => $verifyResult,
            ]);

            // Load relationships
            $schedule = $payment->loan_schedule_id ? LoanSchedule::find($payment->loan_schedule_id) : null;
            $loan = Loan::find($payment->loan_id);

            // Update schedule if exists
            if ($schedule) {
                $newPaidAmount = $schedule->paid_amount + $payment->amount;
                
                $schedule->update([
                    'paid_amount' => $newPaidAmount,
                    'paid_at' => now(),
                ]);

                // Update schedule status
                if ($newPaidAmount >= $schedule->amount_due) {
                    $schedule->update(['status' => LoanSchedule::STATUS_PAID]);
                } else {
                    $schedule->update(['status' => LoanSchedule::STATUS_PARTIAL]);
                }
                
                Log::info('Schedule updated', [
                    'schedule_id' => $schedule->id,
                    'paid_amount' => $newPaidAmount,
                    'status' => $schedule->status,
                ]);
            }

            // Update loan balance
            if ($loan) {
                $newRemaining = max(0, $loan->remaining_balance - $payment->amount);
                
                $loan->update([
                    'remaining_balance' => $newRemaining,
                ]);

                // Check if loan is fully paid
                if ($newRemaining <= 0 && $loan->status === Loan::STATUS_ACTIVE) {
                    $loan->update(['status' => Loan::STATUS_PAID]);
                }
                
                Log::info('Loan updated', [
                    'loan_id' => $loan->id,
                    'remaining_balance' => $newRemaining,
                    'status' => $loan->status,
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);
            throw $e;
        }
    }

    /**
     * Fail payment
     */
    public function failPayment(LoanPayment $payment, string $reason): void
    {
        $this->paymentRepository->update($payment->id, [
            'status' => LoanPayment::STATUS_FAILED,
            'notes' => $reason,
        ]);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(int $paymentId, int $userId): ?LoanPayment
    {
        $payment = $this->paymentRepository->find($paymentId);
        
        if (!$payment || $payment->user_id !== $userId) {
            return null;
        }

        return $payment;
    }

    /**
     * Get user's payment history
     */
    public function getUserPaymentHistory(int $userId, int $perPage = 20): array
    {
        $payments = $this->paymentRepository->findByUserId($userId, $perPage);

        return [
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ];
    }
}


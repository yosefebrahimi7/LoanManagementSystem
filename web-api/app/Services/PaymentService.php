<?php

namespace App\Services;

use App\Events\PaymentFailed;
use App\Events\InstallmentPaid;
use App\Events\LoanFullyPaid;
use App\Jobs\ProcessPaymentWalletUpdateJob;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanSchedule;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Services\NotificationService;
use App\Services\ZarinpalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\LoanException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private WalletRepositoryInterface $walletRepository,
        private ZarinpalService $zarinpalService,
        private NotificationService $notificationService
    ) {}

    /**
     * Pay installment directly from wallet (NO GATEWAY NEEDED)
     */
    public function initiatePayment(Loan $loan, int $scheduleId, ?int $amount = null): array
    {
        return DB::transaction(function () use ($loan, $scheduleId, $amount) {
            // Lock schedule for update
            $schedule = LoanSchedule::lockForUpdate()
                ->where('id', $scheduleId)
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

        // Check user's wallet balance with lock
        $user = auth()->user();
        $userWallet = Wallet::lockForUpdate()
            ->where('user_id', $user->id)
            ->where('is_shared', false)
            ->first();
        
        if (!$userWallet) {
            throw LoanException::badRequest('کیف پول شما یافت نشد. لطفاً حساب خود را شارژ کنید.');
        }

        // Convert amount from Tomans to Rials for comparison with wallet balance
        $amountInRials = $amount * 10;
        
        if ($userWallet->balance < $amountInRials) {
            // Notify user about insufficient wallet balance
            $this->notificationService->notifyWalletInsufficient(
                $user,
                $userWallet->balance,
                $amountInRials
            );
            
                throw LoanException::badRequest(
                    'موجودی کیف پول شما کافی نیست. موجودی: ' . 
                    number_format($userWallet->balance / 10, 0) . 
                    ' تومان. مبلغ مورد نیاز: ' . 
                    number_format($amount, 0) . 
                    ' تومان. لطفاً کیف پول خود را شارژ کنید.'
                );
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

        // NO GATEWAY - Pay directly from wallet
        // Lock wallets for atomic operations
        $adminWallet = Wallet::lockForUpdate()
            ->where('is_shared', true)
            ->whereNull('user_id')
            ->first();

        if (!$adminWallet) {
            throw LoanException::badRequest('کیف پول ادمین یافت نشد');
        }

        // Deduct from user's wallet
        $userWallet->decrement('balance', $amountInRials);
        
        // Add to admin's wallet
        $adminWallet->increment('balance', $amountInRials);

        // Update payment status to completed
        $payment->update([
            'payment_method' => LoanPayment::METHOD_WALLET,
            'status' => LoanPayment::STATUS_COMPLETED,
        ]);

        // Update schedule
        $newPaidAmount = $schedule->paid_amount + $amount;
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

        // Update loan balance
        $loan->decrement('remaining_balance', $amount);
        $loan->refresh();

        // Check if loan is fully paid
        if ($loan->remaining_balance <= 0 && $loan->status === Loan::STATUS_ACTIVE) {
            $loan->update(['status' => Loan::STATUS_PAID]);
            $this->notificationService->notifyLoanFullyPaid($user, $loan);
        }

        // Create wallet transactions
        WalletTransaction::create([
            'wallet_id' => $userWallet->id,
            'type' => WalletTransaction::TYPE_DEBIT,
            'amount' => $amountInRials,
            'balance_after' => $userWallet->fresh()->balance,
            'description' => "پرداخت قسط {$schedule->installment_number} وام {$loan->id}",
            'meta' => ['payment_id' => $payment->id, 'loan_id' => $loan->id],
        ]);

        WalletTransaction::create([
            'wallet_id' => $adminWallet->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'amount' => $amountInRials,
            'balance_after' => $adminWallet->fresh()->balance,
            'description' => "دریافت قسط {$schedule->installment_number} وام {$loan->id}",
            'meta' => ['payment_id' => $payment->id, 'user_id' => $user->id, 'loan_id' => $loan->id],
        ]);

        // Clear cache
        Cache::forget("wallet.user.{$user->id}");
        Cache::forget("wallet.balance.{$userWallet->id}");
        Cache::forget("wallet.balance.{$adminWallet->id}");
        Cache::forget("wallet.shared.admin");

        return [
            'success' => true,
            'message' => 'پرداخت با موفقیت انجام شد',
            'payment_id' => $payment->id,
            'schedule_status' => $schedule->status,
            'remaining_balance' => $loan->remaining_balance,
        ];
        });
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
        // Convert amount from Tomans to Rials for Zarinpal
        $amountInRials = $payment->amount * 10;
        return $this->zarinpalService->verifyPayment($payment->gateway_reference, $amountInRials);
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

            // Track if schedule was fully paid
            $wasScheduleFullyPaid = false;
            $wasLoanFullyPaid = false;

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
                    $wasScheduleFullyPaid = true;
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
                    $wasLoanFullyPaid = true;
                }
                
                Log::info('Loan updated', [
                    'loan_id' => $loan->id,
                    'remaining_balance' => $newRemaining,
                    'status' => $loan->status,
                ]);
            }

            DB::commit();

            // Dispatch wallet update job (deduct from user, add to admin)
            $adminWallet = $this->walletRepository->getSharedAdminWallet();
            if ($adminWallet) {
                $amountInRials = $payment->amount * 10; // Convert to Rials
                ProcessPaymentWalletUpdateJob::dispatch(
                    $payment->user_id,
                    $adminWallet->id,
                    $amountInRials,
                    $payment->id
                );
            }

            // Fire events after successful commit
            if ($wasScheduleFullyPaid && $schedule) {
                $schedule->load('loan.user');
                event(new InstallmentPaid($schedule));
            }

            if ($wasLoanFullyPaid && $loan) {
                $loan->load('user');
                event(new LoanFullyPaid($loan));
            }

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

        // Load relationships and fire payment failed event
        $payment->load(['user', 'loan', 'schedule']);
        event(new PaymentFailed($payment));
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


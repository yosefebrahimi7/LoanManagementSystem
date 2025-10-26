<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\PaymentCallbackRequest;
use App\Http\Requests\PaymentHistoryRequest;
use App\Http\Resources\LoanPaymentResource;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests;

    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}

    /**
     * Initialize payment for a loan installment
     */
    public function initiatePayment(PaymentRequest $request, Loan $loan): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            Log::info('Payment initiation started', [
                'user_id' => auth()->id(),
                'loan_id' => $loan->id,
                'schedule_id' => $validated['schedule_id'],
                'amount' => $validated['amount'] ?? 'auto',
            ]);

            $result = $this->paymentService->initiatePayment(
                $loan,
                $validated['schedule_id'],
                $validated['amount'] ?? null
            );

            Log::info('Payment initiation completed', [
                'payment_id' => $result['payment_id'] ?? 'N/A',
                'authority' => $result['authority'] ?? 'N/A',
            ]);

            // Return response directly for payment gateway redirect
            return response()->json($result);

        } catch (\App\Exceptions\LoanException $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'loan_id' => $loan->id,
                'code' => $e->getCode(),
            ]);

            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
            
        } catch (\Exception $e) {
            Log::error('Payment initiation error', [
                'error' => $e->getMessage(),
                'loan_id' => $loan->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('خطا در ایجاد درخواست پرداخت');
        }
    }

    /**
     * Handle callback from payment gateway
     */
    public function callback(PaymentCallbackRequest $request): mixed
    {
        try {
            Log::info('Payment callback received', [
                'authority' => $request->validated()['Authority'],
                'status' => $request->validated()['Status'] ?? 'N/A',
            ]);

            return $this->paymentService->processPaymentCallback($request);

        } catch (\Exception $e) {
            Log::error('Payment callback failed', [
                'error' => $e->getMessage(),
                'authority' => $request->get('Authority', 'N/A'),
            ]);

            return redirect(config('app.frontend_url', 'http://localhost:5174') . '/payment/failed?error=' . urlencode('خطا در پردازش بازگشت از درگاه'));
        }
    }

    /**
     * Get payment status
     */
    public function paymentStatus(LoanPayment $payment): JsonResponse
    {
        try {
            // Authorization handled by model binding policy
            $this->authorize('view', $payment);

            $payment = $this->paymentService->getPaymentStatus(
                $payment->id,
                auth()->id()
            );

            return $this->successResponse(
                new LoanPaymentResource($payment),
                'اطلاعات پرداخت دریافت شد'
            );

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->forbiddenResponse();
            
        } catch (\Exception $e) {
            Log::error('Get payment status failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id ?? 'N/A',
            ]);

            return $this->errorResponse('خطا در دریافت وضعیت پرداخت');
        }
    }

    /**
     * Get user's payment history
     */
    public function paymentHistory(PaymentHistoryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $perPage = $validated['per_page'] ?? 20;

            $result = $this->paymentService->getUserPaymentHistory(
                auth()->id(),
                $perPage
            );

            Log::info('Payment history retrieved', [
                'user_id' => auth()->id(),
                'per_page' => $perPage,
                'total' => $result['meta']['total'] ?? 0,
            ]);

            return $this->successResponse(
                [
                    'data' => LoanPaymentResource::collection($result['data']),
                    'meta' => $result['meta'],
                ],
                'تاریخچه پرداخت‌ها دریافت شد'
            );

        } catch (\Exception $e) {
            Log::error('Get payment history failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse('خطا در دریافت تاریخچه پرداخت');
        }
    }
}

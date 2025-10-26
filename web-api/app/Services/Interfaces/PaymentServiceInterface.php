<?php

namespace App\Services\Interfaces;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;

interface PaymentServiceInterface
{
    /**
     * Initiate payment for a loan installment
     */
    public function initiatePayment(Loan $loan, int $scheduleId, ?int $amount = null): array;

    /**
     * Process payment callback from gateway
     */
    public function processPaymentCallback(Request $request): mixed;

    /**
     * Get payment status
     */
    public function getPaymentStatus(int $paymentId, int $userId): ?LoanPayment;

    /**
     * Get user's payment history
     */
    public function getUserPaymentHistory(int $userId, int $perPage = 20): array;

    /**
     * Verify payment with Zarinpal
     */
    public function verifyPayment(LoanPayment $payment): array;

    /**
     * Complete payment and update related records
     */
    public function completePayment(LoanPayment $payment, array $verifyResult): void;

    /**
     * Fail payment
     */
    public function failPayment(LoanPayment $payment, string $reason): void;
}


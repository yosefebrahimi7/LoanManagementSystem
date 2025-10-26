<?php

namespace App\Repositories\Interfaces;

use App\Models\LoanPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    /**
     * Create a new payment
     */
    public function create(array $data): LoanPayment;

    /**
     * Find payment by ID
     */
    public function find(int $id): ?LoanPayment;

    /**
     * Update payment
     */
    public function update(int $id, array $data): ?LoanPayment;

    /**
     * Delete payment
     */
    public function delete(int $id): bool;

    /**
     * Find payment by gateway reference
     */
    public function findByGatewayReference(string $reference): ?LoanPayment;

    /**
     * Get all payments
     */
    public function all(): Collection;

    /**
     * Get payments by user ID
     */
    public function findByUserId(int $userId, int $perPage = 20): LengthAwarePaginator;

    /**
     * Get payments by loan ID
     */
    public function findByLoanId(int $loanId): Collection;

    /**
     * Get payments by status
     */
    public function findByStatus(string $status): Collection;

    /**
     * Get pending payments
     */
    public function getPendingPayments(): Collection;

    /**
     * Get completed payments
     */
    public function getCompletedPayments(): Collection;

    /**
     * Get failed payments
     */
    public function getFailedPayments(): Collection;

    /**
     * Get payment statistics
     */
    public function getStatistics(): array;
}


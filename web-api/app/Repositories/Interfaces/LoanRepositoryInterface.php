<?php

namespace App\Repositories\Interfaces;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Collection;

interface LoanRepositoryInterface
{
    /**
     * Create a new loan
     */
    public function create(array $data): Loan;

    /**
     * Find loan by ID
     */
    public function find(int $id): ?Loan;

    /**
     * Update loan
     */
    public function update(int $id, array $data): ?Loan;

    /**
     * Delete loan
     */
    public function delete(int $id): bool;

    /**
     * Get all loans
     */
    public function all(): Collection;

    /**
     * Get loans by user ID
     */
    public function findByUserId(int $userId): Collection;

    /**
     * Get loans by status
     */
    public function findByStatus(string $status): Collection;

    /**
     * Get pending loans
     */
    public function getPendingLoans(): Collection;

    /**
     * Get active loans
     */
    public function getActiveLoans(): Collection;

    /**
     * Get loans with schedules
     */
    public function getLoansWithSchedules(): Collection;

    /**
     * Get loans with payments
     */
    public function getLoansWithPayments(): Collection;

    /**
     * Get loan statistics
     */
    public function getLoanStatistics(): array;
}

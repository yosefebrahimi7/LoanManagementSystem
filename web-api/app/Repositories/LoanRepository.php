<?php

namespace App\Repositories;

use App\Models\Loan;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LoanRepository implements LoanRepositoryInterface
{
    protected $model;

    public function __construct(Loan $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new loan
     */
    public function create(array $data): Loan
    {
        return $this->model->create($data);
    }

    /**
     * Find loan by ID
     */
    public function find(int $id): ?Loan
    {
        return $this->model->find($id);
    }

    /**
     * Update loan
     */
    public function update(int $id, array $data): ?Loan
    {
        $loan = $this->model->find($id);
        if ($loan) {
            $loan->update($data);
            // Clear cache when loan is updated
            $this->clearLoanCache($loan);
        }
        return $loan;
    }

    /**
     * Delete loan
     */
    public function delete(int $id): bool
    {
        $loan = $this->model->find($id);
        if ($loan) {
            $this->clearLoanCache($loan);
            return $loan->delete();
        }
        return false;
    }

    /**
     * Get all loans
     */
    public function all(): Collection
    {
        return Cache::remember('loans.all', 300, function () {
            return $this->model->with(['user', 'schedules', 'payments', 'approvedBy'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get loans by user ID
     */
    public function findByUserId(int $userId): Collection
    {
        return Cache::remember("loans.user.{$userId}", 300, function () use ($userId) {
            return $this->model->where('user_id', $userId)
                ->with(['schedules', 'payments'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get loans by status
     */
    public function findByStatus(string $status): Collection
    {
        return Cache::remember("loans.status.{$status}", 300, function () use ($status) {
            return $this->model->where('status', $status)
                ->with(['user', 'schedules', 'payments'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get pending loans
     */
    public function getPendingLoans(): Collection
    {
        return $this->findByStatus(Loan::STATUS_PENDING);
    }

    /**
     * Get active loans
     */
    public function getActiveLoans(): Collection
    {
        return $this->findByStatus(Loan::STATUS_ACTIVE);
    }

    /**
     * Get loans with schedules
     */
    public function getLoansWithSchedules(): Collection
    {
        return Cache::remember('loans.with_schedules', 300, function () {
            return $this->model->with(['schedules' => function ($query) {
                $query->orderBy('installment_number');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        });
    }

    /**
     * Get loans with payments
     */
    public function getLoansWithPayments(): Collection
    {
        return Cache::remember('loans.with_payments', 300, function () {
            return $this->model->with(['payments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        });
    }

    /**
     * Get loan statistics
     */
    public function getLoanStatistics(): array
    {
        return Cache::remember('loans.statistics', 600, function () {
            return [
                'total_loans' => $this->model->count(),
                'pending_loans' => $this->model->where('status', Loan::STATUS_PENDING)->count(),
                'approved_loans' => $this->model->where('status', Loan::STATUS_APPROVED)->count(),
                'rejected_loans' => $this->model->where('status', Loan::STATUS_REJECTED)->count(),
                'active_loans' => $this->model->where('status', Loan::STATUS_ACTIVE)->count(),
                'delinquent_loans' => $this->model->where('status', Loan::STATUS_DELINQUENT)->count(),
                'paid_loans' => $this->model->where('status', Loan::STATUS_PAID)->count(),
                'total_amount' => $this->model->where('status', Loan::STATUS_APPROVED)->sum('amount'),
                'monthly_loans' => $this->model->whereMonth('created_at', now()->month)->count(),
                'monthly_amount' => $this->model->whereMonth('created_at', now()->month)->sum('amount'),
            ];
        });
    }

    /**
     * Clear loan-related cache
     */
    private function clearLoanCache(Loan $loan): void
    {
        Cache::forget('loans.all');
        Cache::forget("loans.user.{$loan->user_id}");
        Cache::forget("loans.status.{$loan->status}");
        Cache::forget('loans.with_schedules');
        Cache::forget('loans.with_payments');
        Cache::forget('loans.statistics');
    }
}

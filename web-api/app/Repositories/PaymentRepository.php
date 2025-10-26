<?php

namespace App\Repositories;

use App\Models\LoanPayment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    protected $model;

    public function __construct(LoanPayment $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new payment
     */
    public function create(array $data): LoanPayment
    {
        return $this->model->create($data);
    }

    /**
     * Find payment by ID
     */
    public function find(int $id): ?LoanPayment
    {
        return $this->model->with(['loan', 'user', 'schedule'])->find($id);
    }

    /**
     * Update payment
     */
    public function update(int $id, array $data): ?LoanPayment
    {
        $payment = $this->model->find($id);
        if ($payment) {
            $payment->update($data);
        }
        return $payment;
    }

    /**
     * Delete payment
     */
    public function delete(int $id): bool
    {
        $payment = $this->model->find($id);
        if ($payment) {
            return $payment->delete();
        }
        return false;
    }

    /**
     * Find payment by gateway reference
     */
    public function findByGatewayReference(string $reference): ?LoanPayment
    {
        return $this->model->with(['loan', 'user', 'schedule'])
            ->where('gateway_reference', $reference)
            ->first();
    }

    /**
     * Get all payments
     */
    public function all(): Collection
    {
        return $this->model->with(['loan', 'user', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payments by user ID
     */
    public function findByUserId(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->with(['loan', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get payments by loan ID
     */
    public function findByLoanId(int $loanId): Collection
    {
        return $this->model->where('loan_id', $loanId)
            ->with(['user', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payments by status
     */
    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['loan', 'user', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments(): Collection
    {
        return $this->findByStatus(LoanPayment::STATUS_PENDING);
    }

    /**
     * Get completed payments
     */
    public function getCompletedPayments(): Collection
    {
        return $this->findByStatus(LoanPayment::STATUS_COMPLETED);
    }

    /**
     * Get failed payments
     */
    public function getFailedPayments(): Collection
    {
        return $this->findByStatus(LoanPayment::STATUS_FAILED);
    }

    /**
     * Get payment statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_payments' => $this->model->count(),
            'pending_payments' => $this->model->where('status', LoanPayment::STATUS_PENDING)->count(),
            'completed_payments' => $this->model->where('status', LoanPayment::STATUS_COMPLETED)->count(),
            'failed_payments' => $this->model->where('status', LoanPayment::STATUS_FAILED)->count(),
            'total_amount' => $this->model->where('status', LoanPayment::STATUS_COMPLETED)->sum('amount'),
            'monthly_payments' => $this->model->whereMonth('created_at', now()->month)->count(),
            'monthly_amount' => $this->model->whereMonth('created_at', now()->month)->sum('amount'),
        ];
    }
}


<?php

namespace App\Policies;

use App\Models\LoanPayment;
use App\Models\Loan;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, LoanPayment $payment): bool
    {
        return $user->is_active && ($user->id === $payment->user_id || $user->isAdmin());
    }

    /**
     * Determine whether the user can create payments.
     */
    public function create(User $user, Loan $loan): bool
    {
        return $user->is_active && 
               $user->id === $loan->user_id && 
               in_array($loan->status, [Loan::STATUS_APPROVED, Loan::STATUS_ACTIVE, Loan::STATUS_DELINQUENT]);
    }

    /**
     * Determine whether the user can update the payment.
     */
    public function update(User $user, LoanPayment $payment): bool
    {
        // Only user who created the payment can update it (for status changes, etc.)
        return $user->is_active && $user->id === $payment->user_id;
    }

    /**
     * Determine whether the user can delete the payment.
     */
    public function delete(User $user, LoanPayment $payment): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    /**
     * Determine whether the user can view payment history.
     */
    public function viewHistory(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can cancel payment.
     */
    public function cancel(User $user, LoanPayment $payment): bool
    {
        return $user->is_active && 
               $user->id === $payment->user_id && 
               $payment->status === LoanPayment::STATUS_PENDING;
    }

    /**
     * Determine whether the user can refund payment.
     */
    public function refund(User $user, LoanPayment $payment): bool
    {
        return $user->is_active && $user->isAdmin() && $payment->status === LoanPayment::STATUS_COMPLETED;
    }
}


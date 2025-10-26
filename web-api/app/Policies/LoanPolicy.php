<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Loan $loan): bool
    {
        return $user->is_active && ($user->id === $loan->user_id || $user->isAdmin());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Loan $loan): bool
    {
        return $user->is_active && ($user->id === $loan->user_id || $user->isAdmin());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Loan $loan): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    /**
     * Determine whether the user can approve the loan.
     */
    public function approve(User $user, Loan $loan): bool
    {
        return $user->is_active && $user->isAdmin() && $loan->status === Loan::STATUS_PENDING;
    }

    /**
     * Determine whether the user can reject the loan.
     */
    public function reject(User $user, Loan $loan): bool
    {
        return $user->is_active && $user->isAdmin() && $loan->status === Loan::STATUS_PENDING;
    }

    /**
     * Determine whether the user can make payments for the loan.
     */
    public function makePayment(User $user, Loan $loan): bool
    {
        return $user->is_active && 
               $user->id === $loan->user_id && 
               in_array($loan->status, [Loan::STATUS_APPROVED, Loan::STATUS_ACTIVE, Loan::STATUS_DELINQUENT]);
    }

}

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
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
    public function view(User $user, Wallet $wallet): bool
    {
        // Regular users can only view their own wallet
        if (!$user->is_active) {
            return false;
        }
        
        // If wallet is shared admin wallet
        if ($wallet->is_shared && $wallet->user_id === null) {
            return $user->isAdmin();
        }
        
        return $user->id === $wallet->user_id || $user->isAdmin();
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
    public function update(User $user, Wallet $wallet): bool
    {
        if (!$user->is_active) {
            return false;
        }
        
        // If wallet is shared admin wallet, only admins can update
        if ($wallet->is_shared && $wallet->user_id === null) {
            return $user->isAdmin();
        }
        
        return $user->id === $wallet->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Wallet $wallet): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    /**
     * Determine whether the user can make transactions.
     */
    public function makeTransaction(User $user, Wallet $wallet): bool
    {
        if (!$user->is_active) {
            return false;
        }
        
        // If wallet is shared admin wallet, only admins can make transactions
        if ($wallet->is_shared && $wallet->user_id === null) {
            return $user->isAdmin();
        }
        
        return $user->id === $wallet->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can view transactions.
     */
    public function viewTransactions(User $user, Wallet $wallet): bool
    {
        if (!$user->is_active) {
            return false;
        }
        
        // If wallet is shared admin wallet, only admins can view transactions
        if ($wallet->is_shared && $wallet->user_id === null) {
            return $user->isAdmin();
        }
        
        return $user->id === $wallet->user_id || $user->isAdmin();
    }
}

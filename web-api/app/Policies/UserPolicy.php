<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
    public function view(User $user, User $model): bool
    {
        return $user->is_active && ($user->id === $model->id || $this->isAdmin($user));
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
    public function update(User $user, User $model): bool
    {
        return $user->is_active && ($user->id === $model->id || $this->isAdmin($user));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->is_active && $this->isAdmin($user) && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->is_active && $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->is_active && $this->isAdmin($user) && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can change password.
     */
    public function changePassword(User $user, User $model): bool
    {
        return $user->is_active && $user->id === $model->id;
    }

    /**
     * Determine whether the user can deactivate account.
     */
    public function deactivate(User $user, User $model): bool
    {
        return $user->is_active && $this->isAdmin($user) && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can activate account.
     */
    public function activate(User $user, User $model): bool
    {
        return $user->is_active && $this->isAdmin($user);
    }

    /**
     * Check if user is admin
     */
    private function isAdmin(User $user): bool
    {
        // You can implement admin logic here
        // For now, we'll check if email contains 'admin'
        return str_contains($user->email, 'admin');
    }
}
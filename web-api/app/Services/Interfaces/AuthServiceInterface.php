<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(array $data): array;

    /**
     * Login user
     */
    public function login(array $credentials): array;

    /**
     * Logout user
     */
    public function logout(User $user): bool;

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(User $user): User;

    /**
     * Refresh token
     */
    public function refreshToken(User $user): array;

    /**
     * Create token for user
     */
    public function createToken(User $user): string;
}

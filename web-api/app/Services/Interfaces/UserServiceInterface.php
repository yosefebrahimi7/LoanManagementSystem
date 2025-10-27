<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface UserServiceInterface
{
    /**
     * Get all users
     */
    public function getAllUsers(): array;

    /**
     * Get user by ID
     */
    public function getUserById(int $id): User;

    /**
     * Create a new user
     */
    public function createUser(array $data): User;

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): User;

    /**
     * Delete user
     */
    public function deleteUser(int $id): void;

    /**
     * Toggle user status
     */
    public function toggleUserStatus(int $id): User;
}


<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by ID
     */
    public function find(int $id): ?User;

    /**
     * Update user
     */
    public function update(int $id, array $data): User;

    /**
     * Delete user
     */
    public function delete(int $id): bool;

    /**
     * Get all users
     */
    public function all(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Check if user exists by email
     */
    public function existsByEmail(string $email): bool;
}

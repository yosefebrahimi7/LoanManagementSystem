<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): User
    {
        $user = $this->model->find($id);
        if ($user) {
            $user->update($data);
        }
        return $user;
    }

    /**
     * Delete user
     */
    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * Get all users
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get paginated users
     */
    public function paginate(int $perPage = 15)
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Check if user exists by email
     */
    public function existsByEmail(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
}

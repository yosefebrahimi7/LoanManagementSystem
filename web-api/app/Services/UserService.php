<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->all()->all();
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            throw new AuthException('کاربر یافت نشد', 404);
        }

        return $user;
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        // Hash password
        $data['password'] = Hash::make($data['password']);
        
        return $this->userRepository->create($data);
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $data): User
    {
        // Check if user exists
        $user = $this->getUserById($id);

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): void
    {
        $user = $this->getUserById($id);

        // Prevent deleting admin users
        if ($user->isAdmin()) {
            throw new AuthException('امکان حذف کاربر ادمین وجود ندارد', 403);
        }

        $this->userRepository->delete($id);
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(int $id): User
    {
        $user = $this->getUserById($id);

        // Prevent toggling status for admin users
        if ($user->isAdmin()) {
            throw new AuthException('امکان تغییر وضعیت کاربر ادمین وجود ندارد', 403);
        }

        // Toggle status
        $isActive = !$user->is_active;
        $this->userRepository->update($id, ['is_active' => $isActive]);

        return $this->getUserById($id);
    }
}


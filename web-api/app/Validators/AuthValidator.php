<?php

namespace App\Validators;

use App\Repositories\Interfaces\UserRepositoryInterface;

class AuthValidator
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Validate email uniqueness
     */
    public function validateEmailUniqueness(string $email, ?int $excludeUserId = null): bool
    {
        $query = $this->userRepository->getModel()->where('email', $email);
        
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return !$query->exists();
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'رمز عبور باید حداقل 8 کاراکتر باشد';
        }

        if (!preg_match('/[A-Za-z]/', $password)) {
            $errors[] = 'رمز عبور باید شامل حروف باشد';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'رمز عبور باید شامل اعداد باشد';
        }

        return $errors;
    }

    /**
     * Validate user status
     */
    public function validateUserStatus($user): bool
    {
        return $user && $user->is_active;
    }

    /**
     * Validate email format
     */
    public function validateEmailFormat(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

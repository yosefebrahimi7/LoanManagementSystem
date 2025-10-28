<?php

namespace App\Services;

use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Exceptions\AuthException;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Check if user already exists
        if ($this->userRepository->existsByEmail($data['email'])) {
            throw new AuthException('این ایمیل قبلا ثبت شده است', 409);
        }

        // Prepare user data
        $userData = [
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ];

        // Create user
        $user = $this->userRepository->create($userData);

        // Fire user registered event
        event(new UserRegistered($user));

        // Send welcome email
        SendWelcomeEmailJob::dispatch($user);

        // Create token
        $token = $this->createToken($user);

        return [
            'user' => $user, // Return User object, let Resource handle formatting
            'token' => $token,
            'refreshToken' => $token, // For simplicity, using same token as refresh
        ];
    }

    /**
     * Login user
     */
    public function login(array $credentials): array
    {
        // Get user first
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            throw new AuthException('ایمیل یا رمز عبور اشتباه است', 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw new AuthException('حساب کاربری غیرفعال است', 403);
        }

        // Validate credentials
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new AuthException('ایمیل یا رمز عبور اشتباه است', 401);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Fire user logged in event
        event(new UserLoggedIn($user));

        // Create new token
        $token = $this->createToken($user);

        return [
            'user' => $user, // Return User object, let Resource handle formatting
            'token' => $token,
            'refreshToken' => $token, // For simplicity, using same token as refresh
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): bool
    {
        $user->currentAccessToken()->delete();
        return true;
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(User $user): User
    {
        return $user;
    }

    /**
     * Refresh token
     */
    public function refreshToken(User $user): array
    {
        // Revoke current token
        $user->currentAccessToken()->delete();

        // Create new token
        $token = $this->createToken($user);

        return [
            'token' => $token,
            'refreshToken' => $token,
        ];
    }


    /**
     * Create token for user
     */
    public function createToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Format user data for response
     */
    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'isActive' => $user->is_active,
            'role' => $user->role,
            'roleName' => $user->role_name,
            'createdAt' => $user->created_at->toISOString(),
            'updatedAt' => $user->updated_at->toISOString(),
        ];
    }
}

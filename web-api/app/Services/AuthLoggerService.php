<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthLoggerService
{
    const LOG_CHANNEL = 'auth';

    /**
     * Log user registration
     */
    public function logUserRegistration(User $user): void
    {
        Log::channel(self::LOG_CHANNEL)->info('User registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log user login
     */
    public function logUserLogin(User $user): void
    {
        Log::channel(self::LOG_CHANNEL)->info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log user logout
     */
    public function logUserLogout(User $user): void
    {
        Log::channel(self::LOG_CHANNEL)->info('User logged out', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log failed login attempt
     */
    public function logFailedLogin(string $email, string $reason = 'Invalid credentials'): void
    {
        Log::channel(self::LOG_CHANNEL)->warning('Failed login attempt', [
            'email' => $email,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log token refresh
     */
    public function logTokenRefresh(User $user): void
    {
        Log::channel(self::LOG_CHANNEL)->info('Token refreshed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log account lockout
     */
    public function logAccountLockout(string $email): void
    {
        Log::channel(self::LOG_CHANNEL)->warning('Account locked out', [
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(string $email, string $activity): void
    {
        Log::channel(self::LOG_CHANNEL)->error('Suspicious activity detected', [
            'email' => $email,
            'activity' => $activity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}

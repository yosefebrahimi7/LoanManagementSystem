<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

class AuthRateLimitService
{
    const LOGIN_ATTEMPTS_KEY = 'login_attempts';
    const REGISTRATION_ATTEMPTS_KEY = 'registration_attempts';
    const TOKEN_REFRESH_ATTEMPTS_KEY = 'token_refresh_attempts';

    const MAX_LOGIN_ATTEMPTS = 5;
    const MAX_REGISTRATION_ATTEMPTS = 3;
    const MAX_TOKEN_REFRESH_ATTEMPTS = 10;

    const DECAY_MINUTES = 15;

    /**
     * Check if login attempts are too many
     */
    public function tooManyLoginAttempts(string $email): bool
    {
        $key = self::LOGIN_ATTEMPTS_KEY . ':' . $email;
        return RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS);
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts(string $email): int
    {
        $key = self::LOGIN_ATTEMPTS_KEY . ':' . $email;
        return RateLimiter::hit($key, self::DECAY_MINUTES * 60);
    }

    /**
     * Clear login attempts
     */
    public function clearLoginAttempts(string $email): void
    {
        $key = self::LOGIN_ATTEMPTS_KEY . ':' . $email;
        RateLimiter::clear($key);
    }

    /**
     * Get remaining login attempts
     */
    public function remainingLoginAttempts(string $email): int
    {
        $key = self::LOGIN_ATTEMPTS_KEY . ':' . $email;
        return RateLimiter::remaining($key, self::MAX_LOGIN_ATTEMPTS);
    }

    /**
     * Check if registration attempts are too many
     */
    public function tooManyRegistrationAttempts(string $ip): bool
    {
        $key = self::REGISTRATION_ATTEMPTS_KEY . ':' . $ip;
        return RateLimiter::tooManyAttempts($key, self::MAX_REGISTRATION_ATTEMPTS);
    }

    /**
     * Increment registration attempts
     */
    public function incrementRegistrationAttempts(string $ip): int
    {
        $key = self::REGISTRATION_ATTEMPTS_KEY . ':' . $ip;
        return RateLimiter::hit($key, self::DECAY_MINUTES * 60);
    }

    /**
     * Clear registration attempts
     */
    public function clearRegistrationAttempts(string $ip): void
    {
        $key = self::REGISTRATION_ATTEMPTS_KEY . ':' . $ip;
        RateLimiter::clear($key);
    }

    /**
     * Check if token refresh attempts are too many
     */
    public function tooManyTokenRefreshAttempts(string $ip): bool
    {
        $key = self::TOKEN_REFRESH_ATTEMPTS_KEY . ':' . $ip;
        return RateLimiter::tooManyAttempts($key, self::MAX_TOKEN_REFRESH_ATTEMPTS);
    }

    /**
     * Increment token refresh attempts
     */
    public function incrementTokenRefreshAttempts(string $ip): int
    {
        $key = self::TOKEN_REFRESH_ATTEMPTS_KEY . ':' . $ip;
        return RateLimiter::hit($key, self::DECAY_MINUTES * 60);
    }

    /**
     * Clear token refresh attempts
     */
    public function clearTokenRefreshAttempts(string $ip): void
    {
        $key = self::TOKEN_REFRESH_ATTEMPTS_KEY . ':' . $ip;
        RateLimiter::clear($key);
    }

    /**
     * Get lockout time remaining
     */
    public function getLockoutTimeRemaining(string $email): int
    {
        $key = self::LOGIN_ATTEMPTS_KEY . ':' . $email;
        return RateLimiter::availableIn($key);
    }
}

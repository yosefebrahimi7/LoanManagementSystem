<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AuthCacheService
{
    const CACHE_PREFIX = 'auth_';
    const USER_CACHE_TTL = 3600; // 1 hour
    const TOKEN_CACHE_TTL = 1800; // 30 minutes
    const LOGIN_ATTEMPTS_TTL = 900; // 15 minutes

    /**
     * Cache user data
     */
    public function cacheUser(int $userId, array $userData): void
    {
        $key = self::CACHE_PREFIX . 'user_' . $userId;
        Cache::put($key, $userData, self::USER_CACHE_TTL);
    }

    /**
     * Get cached user data
     */
    public function getCachedUser(int $userId): ?array
    {
        $key = self::CACHE_PREFIX . 'user_' . $userId;
        return Cache::get($key);
    }

    /**
     * Clear user cache
     */
    public function clearUserCache(int $userId): void
    {
        $key = self::CACHE_PREFIX . 'user_' . $userId;
        Cache::forget($key);
    }

    /**
     * Cache token data
     */
    public function cacheToken(string $token, array $tokenData): void
    {
        $key = self::CACHE_PREFIX . 'token_' . $token;
        Cache::put($key, $tokenData, self::TOKEN_CACHE_TTL);
    }

    /**
     * Get cached token data
     */
    public function getCachedToken(string $token): ?array
    {
        $key = self::CACHE_PREFIX . 'token_' . $token;
        return Cache::get($key);
    }

    /**
     * Clear token cache
     */
    public function clearTokenCache(string $token): void
    {
        $key = self::CACHE_PREFIX . 'token_' . $token;
        Cache::forget($key);
    }

    /**
     * Track login attempts
     */
    public function trackLoginAttempt(string $email): int
    {
        $key = self::CACHE_PREFIX . 'attempts_' . $email;
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, self::LOGIN_ATTEMPTS_TTL);
        return $attempts;
    }

    /**
     * Get login attempts
     */
    public function getLoginAttempts(string $email): int
    {
        $key = self::CACHE_PREFIX . 'attempts_' . $email;
        return Cache::get($key, 0);
    }

    /**
     * Clear login attempts
     */
    public function clearLoginAttempts(string $email): void
    {
        $key = self::CACHE_PREFIX . 'attempts_' . $email;
        Cache::forget($key);
    }

    /**
     * Check if user is locked out
     */
    public function isUserLockedOut(string $email): bool
    {
        $attempts = $this->getLoginAttempts($email);
        $maxAttempts = config('auth_settings.login.max_attempts', 5);
        return $attempts >= $maxAttempts;
    }

    /**
     * Clear all auth cache
     */
    public function clearAllAuthCache(): void
    {
        $pattern = self::CACHE_PREFIX . '*';
        $keys = Cache::getRedis()->keys($pattern);
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
}

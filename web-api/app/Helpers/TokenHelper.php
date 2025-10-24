<?php

namespace App\Helpers;

use App\Models\User;

class TokenHelper
{
    /**
     * Create a new token for user
     */
    public static function createToken(User $user, string $name = 'auth_token'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    /**
     * Revoke all tokens for user
     */
    public static function revokeAllTokens(User $user): bool
    {
        return $user->tokens()->delete();
    }

    /**
     * Revoke current token for user
     */
    public static function revokeCurrentToken(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    /**
     * Check if user has valid token
     */
    public static function hasValidToken(User $user): bool
    {
        return $user->tokens()->where('expires_at', '>', now())->exists();
    }

    /**
     * Get token abilities
     */
    public static function getTokenAbilities(): array
    {
        return ['*']; // All abilities
    }
}

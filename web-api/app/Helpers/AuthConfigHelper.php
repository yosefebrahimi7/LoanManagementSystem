<?php

namespace App\Helpers;

class AuthConfigHelper
{
    /**
     * Get password requirements
     */
    public static function getPasswordRequirements(): array
    {
        return config('auth_settings.password', []);
    }

    /**
     * Get token settings
     */
    public static function getTokenSettings(): array
    {
        return config('auth_settings.token', []);
    }

    /**
     * Get registration settings
     */
    public static function getRegistrationSettings(): array
    {
        return config('auth_settings.registration', []);
    }

    /**
     * Get login settings
     */
    public static function getLoginSettings(): array
    {
        return config('auth_settings.login', []);
    }

    /**
     * Get security settings
     */
    public static function getSecuritySettings(): array
    {
        return config('auth_settings.security', []);
    }

    /**
     * Check if email verification is required
     */
    public static function requiresEmailVerification(): bool
    {
        return config('auth_settings.registration.require_email_verification', false);
    }

    /**
     * Check if auto activation is enabled
     */
    public static function isAutoActivationEnabled(): bool
    {
        return config('auth_settings.registration.auto_activate', true);
    }

    /**
     * Get minimum password length
     */
    public static function getMinPasswordLength(): int
    {
        return config('auth_settings.password.min_length', 8);
    }

    /**
     * Get token expiration hours
     */
    public static function getTokenExpirationHours(): int
    {
        return config('auth_settings.token.expiration_hours', 24);
    }

    /**
     * Get max login attempts
     */
    public static function getMaxLoginAttempts(): int
    {
        return config('auth_settings.login.max_attempts', 5);
    }
}

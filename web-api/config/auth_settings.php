<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | This file contains additional authentication settings for the application.
    |
    */

    'password' => [
        'min_length' => 8,
        'require_uppercase' => false,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
    ],

    'token' => [
        'expiration_hours' => 24,
        'refresh_threshold_hours' => 1,
        'max_tokens_per_user' => 5,
    ],

    'registration' => [
        'require_email_verification' => false,
        'auto_activate' => true,
        'send_welcome_email' => false,
    ],

    'login' => [
        'max_attempts' => 5,
        'lockout_duration_minutes' => 15,
        'remember_me_days' => 30,
    ],

    'security' => [
        'force_logout_on_password_change' => true,
        'log_auth_events' => true,
        'require_strong_passwords' => false,
    ],
];

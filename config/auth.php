<?php

declare(strict_types=1);

return [
    'jwt_secret'        => 'psnf-erp-jwt-secret-key-2026-change-me',
    'jwt_ttl'           => 3600,           // 1 hour
    'jwt_refresh_ttl'   => 604800,         // 7 days
    'otp_length'        => 6,
    'otp_ttl'           => 600,            // 10 minutes
    'password_reset_ttl'=> 3600,           // 1 hour
    'max_login_attempts'=> 5,
    'lockout_minutes'   => 15,
    'session_timeout'   => 86400,          // 24 hours
    'remember_me_days'  => 30,
    '2fa_enabled'       => false,          // Ready for 2FA
    'verify_email'      => false,          // Email verification
];

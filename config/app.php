<?php

declare(strict_types=1);

return [
    'name'        => 'PSNF ERP',
    'version'     => '1.0.0',
    // Debug output (stack traces, display_errors) only when APP_DEBUG=1 is set
    // in the environment (e.g. SetEnv APP_DEBUG 1 in a local Apache vhost).
    'debug'       => in_array(strtolower((string) getenv('APP_DEBUG')), ['1', 'true', 'on', 'yes'], true),
    'base_url'    => '',
    'base_path'   => 'erp',
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en',
    'uploads_dir' => ROOT_PATH . '/storage/uploads',

    // Reverse proxies (exact REMOTE_ADDR values) whose X-Forwarded-For /
    // CF-Connecting-IP / X-Forwarded-Host headers may be trusted.
    // Leave empty unless the app sits behind a proxy you control.
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) getenv('TRUSTED_PROXIES'))))),

    'session' => [
        'lifetime' => 86400,       // 24 hours
        'timeout'  => 86400,       // 24 hours idle
        'secure'   => null,        // null = automatic (Secure flag when the request is HTTPS)
        'samesite' => 'Lax',
    ],

    'mail' => [
        'driver'     => 'smtp',
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'username'   => '',
        'password'   => '',
        'from_email' => 'noreply@psnf.edu',
        'from_name'  => 'PSNF ERP System',
    ],
];

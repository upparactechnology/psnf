<?php

declare(strict_types=1);

return [
    'name'        => 'PSNF ERP',
    'version'     => '1.0.0',
    'debug'       => true, // Set to false in production
    'base_url'    => 'http://localhost/psnf/public',
    'base_path'   => 'psnf/public',
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en',
    'uploads_dir' => ROOT_PATH . '/storage/uploads',

    'session' => [
        'lifetime' => 7200,       // 2 hours
        'timeout'  => 1200,       // 20 minutes idle
        'secure'   => false,
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

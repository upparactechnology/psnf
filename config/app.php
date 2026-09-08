<?php

declare(strict_types=1);

return [
    'name'        => 'PSNF ERP',
    'version'     => '1.0.0',
    'debug'       => true, // Set to false in production
    'base_url'    => '',
    'base_path'   => '',
    'timezone'    => 'Asia/Kolkata',
    'locale'      => 'en',
    'uploads_dir' => ROOT_PATH . '/storage/uploads',

    'session' => [
        'lifetime' => 86400,       // 24 hours
        'timeout'  => 86400,       // 24 hours idle
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

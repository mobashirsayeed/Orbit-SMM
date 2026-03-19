<?php

return [
    'default' => env('MAIL_MAILER', 'failover'),
    'mailers' => [
        // Primary: SendGrid
        'sendgrid' => [
            'transport' => 'smtp',
            'host' => 'smtp.sendgrid.net',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'apikey',
            'password' => env('SENDGRID_API_KEY'),
            'timeout' => 10,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        // Fallback: 20i SMTP
        'fallback' => [
            'transport' => 'smtp',
            'host' => env('MAIL_FALLBACK_HOST', 'smtp.20i.com'),
            'port' => env('MAIL_FALLBACK_PORT', 587),
            'encryption' => env('MAIL_FALLBACK_ENCRYPTION', 'tls'),
            'username' => env('MAIL_FALLBACK_USERNAME'),
            'password' => env('MAIL_FALLBACK_PASSWORD'),
            'timeout' => 30,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        // Log for debugging
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        // Failover Driver
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'sendgrid',
                'fallback',
            ],
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@yourdomain.com'),
        'name' => env('MAIL_FROM_NAME', 'Orbit'),
    ],
];

<?php

return [
    'strategy' => env('LAREMAIL_STRATEGY', 'round_robin'), // round_robin|fixed
    'default_provider' => env('LAREMAIL_DEFAULT', 'sendgrid_1'),

    'providers' => [
        'sendgrid_1' => [
            'driver' => 'sendgrid',
            'api_key' => env('SENDGRID_API_KEY'),
            'from_email' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
            'from_name'  => env('MAIL_FROM_NAME', 'No Reply'),
            'sandbox_mode' => env('SENDGRID_SANDBOX', false),
        ],
    ],

    'webhook' => [
        'route' => '/laravel-email/webhook/sendgrid',
    ],

    'routes' => [
        'track' => '/laravel-email/t/{token}',
        'unsubscribe' => '/laravel-email/u/{token}',
    ],

    'rate_limit_per_minute' => env('LAREMAIL_RPM', 600),
    'list_unsubscribe' => true,
];

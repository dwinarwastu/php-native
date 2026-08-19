<?php

declare(strict_types=1);

return [
    'name' => 'PHP Native',
    'env' => 'development',
    'version' => '1.0.0',

    'auth' => [
        'header' => 'HTTP_X_API_KEY',
        'api_key' => 'c3a08deba2285418da7cc14c1b22efec',
    ],

    'cors' => [
        'allowed_origins' => '*',
        'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'allowed_headers' => 'Content-Type, X-API-Key, Authorization',
    ],
];

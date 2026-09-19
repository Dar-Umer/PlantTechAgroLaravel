<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_unique([
        env('APP_URL', 'http://localhost'),
        env('FRONTEND_URL'),
        'http://localhost:3000',
        'http://127.0.0.1:8000',
    ]))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'X-CSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Bearer-token API must not use cookies; keep false to avoid CSRF via wildcard origins.
    'supports_credentials' => false,
];

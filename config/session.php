<?php

return [

    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,

    // File session storage (default)
    'files' => storage_path('framework/sessions'),

    // For database/redis drivers; keep null unless you use them
    'connection' => null,
    'table' => 'sessions',
    'store' => null,

    // Lottery for garbage collection: [chance, out_of]
    'lottery' => [2, 100],

    // Cookie name & scope
    'cookie' => env('SESSION_COOKIE', 'laravel_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', null),

    // Security flags
    'secure' => env('SESSION_SECURE_COOKIE', null), // true on HTTPS
    'http_only' => true,
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
];

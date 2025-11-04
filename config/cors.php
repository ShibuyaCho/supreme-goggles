<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration supports Laravel Sanctum cookie-based authentication.
    | It ensures your frontend (React/Vue/etc.) can send requests with
    | credentials (cookies) from localhost:3000 or any configured domain.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Include all routes that should allow cross-origin access.
    | Be sure to include:
    | - sanctum/csrf-cookie  → required by Sanctum
    | - login, logout        → for SPA auth
    | - api/*                → for your API routes
    |
    */
    'paths' => [
        'api/*',
        'login',
        'logout',
        'sanctum/csrf-cookie',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Allow all HTTP methods during development.
    | Restrict to specific methods (e.g., ['GET', 'POST']) if needed.
    |
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Define which frontend origins can access your backend.
    | For local dev, include both localhost and 127.0.0.1 variants.
    | For production, add your real domain (e.g. https://app.yourdomain.com).
    |
    */
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost',
        'http://127.0.0.1',
        'https://your-production-domain.com', // add your production domain here
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | You can use wildcard patterns here if needed.
    | Usually left empty unless you want to allow dynamic subdomains.
    |
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Which request headers are allowed from the frontend.
    | Keep ['*'] for simplicity, or restrict to ['Content-Type', 'X-CSRF-TOKEN', 'X-Requested-With'].
    |
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | You can expose specific headers to the frontend.
    | Commonly empty unless you need to access custom headers in JS.
    |
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) the results of a preflight request can be cached.
    |
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | IMPORTANT: Sanctum cookie-based auth REQUIRES this to be true.
    | It allows browsers to include cookies (session + XSRF-TOKEN).
    |
    */
    'supports_credentials' => true,
];

<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // Custom security middleware
        \App\Http\Middleware\SecurityHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            /*
            |--------------------------------------------------------------------------
            | Sanctum Integration for SPA + API
            |--------------------------------------------------------------------------
            | This ensures that stateful frontend requests share the same session cookies
            | and CSRF protection as 'web' routes when appropriate.
            */
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        // Server-rendered POS pages -> session guard
        'pos' => [
            'web',
            'auth', // <— changed from auth:sanctum
            'throttle:pos',
            // Use the variant your RoleMiddleware expects:
            'role:cashier,budtender,manager,admin',
            // If your RoleMiddleware expects a single piped string, use this instead:
            // 'role:cashier|budtender|manager|admin',
        ],

        // Server-rendered Admin pages -> session guard
        'admin' => [
            'web',
            'auth', // <— changed from auth:sanctum
            'throttle:admin',
            'role:admin',
            'ip_whitelist',
        ],

        // Lock down API routes with Sanctum
        'secure_api' => [
            'api',
            'auth:sanctum',
            'auth_rate_limit',
        ],
    ];

    /**
     * Middleware aliases for routes.
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // Custom
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'auth_rate_limit' => \App\Http\Middleware\AuthRateLimit::class,
        'ip_whitelist' => \App\Http\Middleware\IpWhitelist::class,
        'security_headers' => \App\Http\Middleware\SecurityHeaders::class,
    ];

    /**
     * Priority-sorted middleware execution order.
     */
    protected $middlewarePriority = [
        \App\Http\Middleware\SecurityHeaders::class,
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\RoleMiddleware::class,
        \App\Http\Middleware\CheckPermission::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}

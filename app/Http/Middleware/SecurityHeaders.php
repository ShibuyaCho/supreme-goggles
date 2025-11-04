<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply in production, or when explicitly forced via config('security.force_headers', false)
        $force = (bool) config('security.force_headers', false);
        $shouldApply = app()->environment('production') || $force;

        if ($shouldApply) {
            $this->addSecurityHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Add comprehensive security headers WITHOUT touching Set-Cookie.
     */
    private function addSecurityHeaders(Response $response, Request $request): void
    {
        $headers = (array) config('security.headers', []);

        // --- Simple headers (only set if provided, otherwise use safe defaults) ---
        $response->headers->set(
            'X-Frame-Options',
            $headers['x_frame_options'] ?? 'SAMEORIGIN',
            false
        );

        $response->headers->set(
            'X-Content-Type-Options',
            $headers['x_content_type_options'] ?? 'nosniff',
            false
        );

        $response->headers->set(
            'Referrer-Policy',
            $headers['referrer_policy'] ?? 'strict-origin-when-cross-origin',
            false
        );

        // Note: modern browsers ignore X-XSS-Protection; set to "0" to disable legacy filtering.
        $response->headers->set(
            'X-XSS-Protection',
            $headers['x_xss_protection'] ?? '0',
            false
        );

        $response->headers->set(
            'Permissions-Policy',
            $headers['permissions_policy'] ?? $this->buildPermissionsPolicy(),
            false
        );

        // --- CSP ---
        $csp = $headers['csp'] ?? $this->buildContentSecurityPolicy();
        if (!empty($csp)) {
            $response->headers->set('Content-Security-Policy', $csp, false);
        }

        // --- HSTS (only when secure) ---
        $hsts = (array) ($headers['hsts'] ?? []);
        if (!empty($hsts['enable']) && $this->isSecureConnection($request)) {
            $value = 'max-age=' . intval($hsts['max_age'] ?? 31536000);
            if (!empty($hsts['include_subdomains'])) {
                $value .= '; includeSubDomains';
            }
            if (!empty($hsts['preload'])) {
                $value .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $value, false);
        }

        // IMPORTANT: Never modify Set-Cookie / SameSite / Secure here
        // to avoid breaking Laravel session cookies.
    }

    /**
     * Build Content Security Policy header (sane defaults; dev-friendly).
     */
    private function buildContentSecurityPolicy(): string
    {
        $isProd = app()->environment('production');

        // Base directives
        $directives = [
            "default-src 'self'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'none'",
            "frame-src 'none'",
            "worker-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "manifest-src 'self'",
        ];

        // Script/style/connect loosening
        if ($isProd) {
            $directives[] = "script-src 'self' 'unsafe-inline'";   // allow inline for e.g. Alpine
            $directives[] = "style-src 'self' 'unsafe-inline'";
            $directives[] = "connect-src 'self'";
        } else {
            // Development: allow eval + ws for Vite, etc.
            $directives[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval'";
            $directives[] = "style-src 'self' 'unsafe-inline'";
            $directives[] = "connect-src 'self' ws: http://localhost:* http://127.0.0.1:*";
        }

        return implode('; ', $directives);
    }

    /**
     * Build Permissions-Policy header (restrictive by default).
     */
    private function buildPermissionsPolicy(): string
    {
        // Key-value comments are explanatory only; header uses just keys.
        $policies = [
            'geolocation=()',
            'camera=()',
            'microphone=()',
            'payment=()',
            'usb=()',
            'vr=()',
            'accelerometer=()',
            'gyroscope=()',
            'magnetometer=()',
            'fullscreen=(self)',
        ];

        return implode(', ', $policies);
    }

    /**
     * Check if the current request should be considered secure for HSTS.
     */
    private function isSecureConnection(Request $request): bool
    {
        // Trust proxy headers if configured via TrustProxies
        return $request->isSecure();
    }
}

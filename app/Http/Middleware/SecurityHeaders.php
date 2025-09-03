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

        // Only apply security headers to non-API routes in production
        if (config('app.env') === 'production' || config('security.force_headers', true)) {
            $this->addSecurityHeaders($response);
        }

        return $response;
    }

    /**
     * Add comprehensive security headers
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = [
            // Prevent page from being displayed in a frame/iframe (clickjacking protection)
            'X-Frame-Options' => 'DENY',
            
            // Prevent MIME type sniffing
            'X-Content-Type-Options' => 'nosniff',
            
            // Enable XSS filtering
            'X-XSS-Protection' => '1; mode=block',
            
            // Referrer policy - only send origin when crossing origins
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Don't send server information
            'Server' => '',
            
            // Remove PHP version info
            'X-Powered-By' => '',
            
            // Prevent caching of sensitive pages
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ];

        // HTTPS-only headers
        if ($this->isSecureConnection()) {
            $headers = array_merge($headers, [
                // Strict Transport Security - force HTTPS for 1 year
                'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
                
                // Expect-CT header for certificate transparency
                'Expect-CT' => 'max-age=86400, enforce',
            ]);
        }

        // Content Security Policy
        $headers['Content-Security-Policy'] = $this->buildContentSecurityPolicy();

        // Permissions Policy (formerly Feature Policy)
        $headers['Permissions-Policy'] = $this->buildPermissionsPolicy();

        // Apply headers to response
        foreach ($headers as $name => $value) {
            if ($value !== '') {
                $response->headers->set($name, $value);
            } else {
                // Remove header if value is empty
                $response->headers->remove($name);
            }
        }
    }

    /**
     * Build Content Security Policy header
     */
    private function buildContentSecurityPolicy(): string
    {
        $domain = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : "'self'";
        
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'", // Allow inline scripts for Alpine.js
            "style-src 'self' 'unsafe-inline'", // Allow inline styles
            "img-src 'self' data: https:",
            "font-src 'self'",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'none'",
            "frame-src 'none'",
            "worker-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "manifest-src 'self'",
        ];

        // In development, be more permissive
        if (config('app.env') !== 'production') {
            $directives = array_map(function($directive) {
                if (strpos($directive, 'script-src') === 0) {
                    return "script-src 'self' 'unsafe-inline' 'unsafe-eval'";
                }
                return $directive;
            }, $directives);
        }

        return implode('; ', $directives);
    }

    /**
     * Build Permissions Policy header
     */
    private function buildPermissionsPolicy(): string
    {
        $policies = [
            'camera=()' => 'Disable camera access',
            'microphone=()' => 'Disable microphone access',
            'geolocation=()' => 'Disable geolocation',
            'interest-cohort=()' => 'Disable FLoC tracking',
            'payment=()' => 'Disable payment API',
            'usb=()' => 'Disable USB API',
            'vr=()' => 'Disable VR API',
            'accelerometer=()' => 'Disable accelerometer',
            'gyroscope=()' => 'Disable gyroscope',
            'magnetometer=()' => 'Disable magnetometer',
            'fullscreen=(self)' => 'Allow fullscreen for same origin only',
        ];

        return implode(', ', array_keys($policies));
    }

    /**
     * Check if the connection is secure (HTTPS)
     */
    private function isSecureConnection(): bool
    {
        return request()->isSecure() || 
               config('app.env') === 'production' || 
               config('security.force_https', false);
    }
}

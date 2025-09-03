<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '5', string $decayMinutes = '5'): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            // Log suspicious activity
            $this->logSuspiciousActivity($request, $key);
            
            return response()->json([
                'error' => 'Too many authentication attempts',
                'message' => 'Please try again later',
                'retry_after' => $retryAfter,
                'blocked_until' => now()->addSeconds($retryAfter)->toISOString()
            ], 429);
        }

        $response = $next($request);

        // If authentication failed, increment the rate limit
        if ($this->isAuthenticationFailure($response)) {
            RateLimiter::hit($key, $decayMinutes * 60);
            
            // Log failed attempt
            Log::warning('Authentication attempt failed', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'attempts_remaining' => $maxAttempts - RateLimiter::attempts($key)
            ]);
        } else {
            // Clear rate limit on successful authentication
            RateLimiter::clear($key);
        }

        return $response;
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Combine IP and user agent for more accurate tracking
        return sha1(
            $request->route()?->getDomain() . '|' .
            $request->ip() . '|' .
            $request->route()?->getName() . '|' .
            substr(md5($request->userAgent() ?? ''), 0, 8)
        );
    }

    /**
     * Determine if the response indicates authentication failure
     */
    protected function isAuthenticationFailure(Response $response): bool
    {
        // Check for 401 Unauthorized or specific error responses
        if ($response->getStatusCode() === 401) {
            return true;
        }

        // Check for JSON responses indicating auth failure
        if ($response->headers->get('content-type') === 'application/json') {
            $content = json_decode($response->getContent(), true);
            
            if (isset($content['error']) && 
                (strpos($content['error'], 'Invalid credentials') !== false ||
                 strpos($content['error'], 'Authentication failed') !== false ||
                 strpos($content['error'], 'Login failed') !== false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log suspicious authentication activity
     */
    protected function logSuspiciousActivity(Request $request, string $key): void
    {
        $attempts = RateLimiter::attempts($key);
        
        Log::warning('Rate limit exceeded for authentication', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'attempts' => $attempts,
            'signature' => $key,
            'country' => $this->getCountryFromIP($request->ip()),
            'headers' => $this->getSafeHeaders($request)
        ]);

        // If attempts are very high, log as critical
        if ($attempts > 20) {
            Log::critical('Potential brute force attack detected', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'user_agent' => $request->userAgent()
            ]);
        }
    }

    /**
     * Get country from IP address (placeholder - integrate with GeoIP service)
     */
    protected function getCountryFromIP(string $ip): ?string
    {
        // This is a placeholder. In production, you might want to integrate
        // with a GeoIP service like MaxMind or ipinfo.io
        return null;
    }

    /**
     * Get safe headers for logging (exclude sensitive information)
     */
    protected function getSafeHeaders(Request $request): array
    {
        $safeHeaders = [];
        $allowedHeaders = [
            'accept',
            'accept-language',
            'accept-encoding',
            'cache-control',
            'connection',
            'host',
            'upgrade-insecure-requests'
        ];

        foreach ($allowedHeaders as $header) {
            if ($request->hasHeader($header)) {
                $safeHeaders[$header] = $request->header($header);
            }
        }

        return $safeHeaders;
    }
}

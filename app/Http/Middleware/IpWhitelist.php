<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$allowedIps): Response
    {
        $clientIp = $this->getClientIp($request);
        
        // Get allowed IPs from configuration if not provided as parameters
        if (empty($allowedIps)) {
            $allowedIps = config('security.admin_ip_whitelist', []);
        }

        // If no whitelist is configured, allow all (log warning)
        if (empty($allowedIps)) {
            if (config('app.env') === 'production') {
                Log::warning('No IP whitelist configured for admin access', [
                    'ip' => $clientIp,
                    'route' => $request->route()?->getName()
                ]);
            }
            return $next($request);
        }

        // Check if IP is whitelisted
        if ($this->isIpAllowed($clientIp, $allowedIps)) {
            return $next($request);
        }

        // Log unauthorized access attempt
        Log::warning('Unauthorized IP attempted admin access', [
            'ip' => $clientIp,
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'allowed_ips' => $allowedIps
        ]);

        // Return 403 Forbidden
        return response()->json([
            'error' => 'Access denied',
            'message' => 'Your IP address is not authorized to access this resource'
        ], 403);
    }

    /**
     * Get the real client IP address
     */
    protected function getClientIp(Request $request): string
    {
        // Check for IP behind proxy/load balancer
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated list (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback to request IP
        return $request->ip();
    }

    /**
     * Check if IP is in the allowed list
     */
    protected function isIpAllowed(string $clientIp, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowedIp) {
            if ($this->matchIp($clientIp, $allowedIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Match IP against pattern (supports CIDR notation and wildcards)
     */
    protected function matchIp(string $clientIp, string $pattern): bool
    {
        // Exact match
        if ($clientIp === $pattern) {
            return true;
        }

        // CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($pattern, '/') !== false) {
            return $this->matchCidr($clientIp, $pattern);
        }

        // Wildcard pattern (e.g., 192.168.1.*)
        if (strpos($pattern, '*') !== false) {
            return $this->matchWildcard($clientIp, $pattern);
        }

        return false;
    }

    /**
     * Match IP against CIDR notation
     */
    protected function matchCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
            !filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int)$mask);
        
        return ($ip & $mask) === ($subnet & $mask);
    }

    /**
     * Match IP against wildcard pattern
     */
    protected function matchWildcard(string $ip, string $pattern): bool
    {
        // Convert wildcard pattern to regex
        $regex = str_replace(['.', '*'], ['\.', '\d+'], $pattern);
        $regex = '/^' . $regex . '$/';
        
        return preg_match($regex, $ip) === 1;
    }
}

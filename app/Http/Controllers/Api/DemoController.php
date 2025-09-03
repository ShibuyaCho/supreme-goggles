<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    /**
     * Ping endpoint to test API connectivity
     */
    public function ping(Request $request): JsonResponse
    {
        $pingMessage = env('PING_MESSAGE', 'ping');
        
        return response()->json([
            'message' => $pingMessage,
            'timestamp' => now()->toISOString(),
            'status' => 'ok'
        ]);
    }
    
    /**
     * Demo endpoint for testing
     */
    public function demo(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Hello from Laravel API',
            'framework' => 'Laravel',
            'version' => app()->version(),
            'environment' => app()->environment(),
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Get API status and information
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'message' => 'Cannabis POS API is running',
            'version' => '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'timestamp' => now()->toISOString(),
            'uptime' => $this->getUptime(),
            'endpoints' => [
                'ping' => '/api/ping',
                'demo' => '/api/demo',
                'status' => '/api/status',
                'products' => '/api/products',
                'customers' => '/api/customers',
                'sales' => '/api/sales',
                'pos' => '/api/pos/*'
            ]
        ]);
    }
    
    /**
     * Get server uptime (simplified)
     */
    private function getUptime(): string
    {
        // This is a simplified uptime calculation
        // In production, you might want to track this more accurately
        $startTime = cache()->remember('server_start_time', 3600, function() {
            return now();
        });
        
        $uptime = now()->diffInSeconds($startTime);
        
        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);
        $seconds = $uptime % 60;
        
        return sprintf('%dd %02dh %02dm %02ds', $days, $hours, $minutes, $seconds);
    }
}

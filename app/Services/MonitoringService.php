<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class MonitoringService
{
    private array $healthChecks = [];
    private array $metrics = [];

    /**
     * Perform comprehensive system health check
     */
    public function healthCheck(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'metrc' => $this->checkMetrc(),
            'disk_space' => $this->checkDiskSpace(),
            'memory' => $this->checkMemory(),
            'log_files' => $this->checkLogFiles(),
        ];

        $overallHealth = array_reduce($checks, function($carry, $check) {
            return $carry && $check['status'] === 'healthy';
        }, true);

        return [
            'overall_status' => $overallHealth ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'environment' => config('app.env'),
        ];
    }

    /**
     * Check database connectivity and performance
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            
            // Test connection
            DB::connection()->getPdo();
            
            // Test query performance
            $result = DB::select('SELECT 1 as test');
            
            $responseTime = (microtime(true) - $start) * 1000; // Convert to ms
            
            // Check slow queries (if > 100ms for simple query)
            $status = $responseTime < 100 ? 'healthy' : 'warning';
            
            return [
                'status' => $result ? $status : 'unhealthy',
                'response_time_ms' => round($responseTime, 2),
                'details' => 'Database connection successful',
            ];
        } catch (Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'details' => 'Database connection failed',
            ];
        }
    }

    /**
     * Check storage system
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health-check-' . time() . '.tmp';
            $testContent = 'Health check test - ' . now()->toISOString();
            
            // Test write
            Storage::put($testFile, $testContent);
            
            // Test read
            $readContent = Storage::get($testFile);
            
            // Test delete
            Storage::delete($testFile);
            
            $success = $readContent === $testContent;
            
            return [
                'status' => $success ? 'healthy' : 'unhealthy',
                'details' => $success ? 'Storage read/write successful' : 'Storage test failed',
            ];
        } catch (Exception $e) {
            Log::error('Storage health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'details' => 'Storage system unavailable',
            ];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health-check-' . time();
            $testValue = 'test-' . uniqid();
            
            // Test cache write
            Cache::put($testKey, $testValue, 60);
            
            // Test cache read
            $cachedValue = Cache::get($testKey);
            
            // Cleanup
            Cache::forget($testKey);
            
            $success = $cachedValue === $testValue;
            
            return [
                'status' => $success ? 'healthy' : 'unhealthy',
                'details' => $success ? 'Cache read/write successful' : 'Cache test failed',
            ];
        } catch (Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'details' => 'Cache system unavailable',
            ];
        }
    }

    /**
     * Check METRC connectivity
     */
    private function checkMetrc(): array
    {
        try {
            if (!config('services.metrc.enabled')) {
                return [
                    'status' => 'disabled',
                    'details' => 'METRC integration is disabled',
                ];
            }

            $metrcService = app(MetrcService::class);
            $result = $metrcService->testConnection();
            
            return [
                'status' => $result['success'] ? 'healthy' : 'unhealthy',
                'details' => $result['message'] ?? 'METRC connection test completed',
                'response_time_ms' => $result['response_time'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('METRC health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'details' => 'METRC service unavailable',
            ];
        }
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace(): array
    {
        try {
            $rootPath = base_path();
            $freeBytes = disk_free_space($rootPath);
            $totalBytes = disk_total_space($rootPath);
            
            $freePercentage = ($freeBytes / $totalBytes) * 100;
            
            $status = 'healthy';
            if ($freePercentage < 10) {
                $status = 'unhealthy';
            } elseif ($freePercentage < 20) {
                $status = 'warning';
            }
            
            return [
                'status' => $status,
                'free_space_gb' => round($freeBytes / (1024 * 1024 * 1024), 2),
                'total_space_gb' => round($totalBytes / (1024 * 1024 * 1024), 2),
                'free_percentage' => round($freePercentage, 1),
                'details' => "Disk space: {$freePercentage}% free",
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'details' => 'Could not check disk space',
            ];
        }
    }

    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $memoryLimit = ini_get('memory_limit');
            
            // Convert memory limit to bytes
            $memoryLimitBytes = $this->convertToBytes($memoryLimit);
            $memoryPercentage = ($memoryUsage / $memoryLimitBytes) * 100;
            
            $status = 'healthy';
            if ($memoryPercentage > 80) {
                $status = 'unhealthy';
            } elseif ($memoryPercentage > 60) {
                $status = 'warning';
            }
            
            return [
                'status' => $status,
                'current_usage_mb' => round($memoryUsage / (1024 * 1024), 2),
                'peak_usage_mb' => round($memoryPeak / (1024 * 1024), 2),
                'limit' => $memoryLimit,
                'usage_percentage' => round($memoryPercentage, 1),
                'details' => "Memory usage: {$memoryPercentage}%",
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'details' => 'Could not check memory usage',
            ];
        }
    }

    /**
     * Check log file sizes and errors
     */
    private function checkLogFiles(): array
    {
        try {
            $logPath = storage_path('logs');
            $logFiles = glob($logPath . '/*.log');
            
            $totalSize = 0;
            $errorCount = 0;
            $recentErrors = [];
            
            foreach ($logFiles as $logFile) {
                $size = filesize($logFile);
                $totalSize += $size;
                
                // Check for recent errors in Laravel log
                if (basename($logFile) === 'laravel.log') {
                    $recentErrors = $this->getRecentLogErrors($logFile);
                    $errorCount = count($recentErrors);
                }
            }
            
            $totalSizeMB = round($totalSize / (1024 * 1024), 2);
            
            $status = 'healthy';
            if ($totalSizeMB > 100) {
                $status = 'warning';
            }
            if ($errorCount > 10) {
                $status = 'unhealthy';
            }
            
            return [
                'status' => $status,
                'total_size_mb' => $totalSizeMB,
                'error_count_24h' => $errorCount,
                'recent_errors' => array_slice($recentErrors, 0, 5), // Last 5 errors
                'details' => "Log files: {$totalSizeMB}MB, {$errorCount} recent errors",
            ];
        } catch (Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage(),
                'details' => 'Could not check log files',
            ];
        }
    }

    /**
     * Get recent errors from log file
     */
    private function getRecentLogErrors(string $logFile, int $hours = 24): array
    {
        try {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            
            $errors = [];
            $cutoffTime = now()->subHours($hours);
            
            foreach ($lines as $line) {
                if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                    // Extract timestamp and check if within timeframe
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                        $logTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
                        if ($logTime->isAfter($cutoffTime)) {
                            $errors[] = trim($line);
                        }
                    }
                }
            }
            
            return $errors;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes(string $value): int
    {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Collect system metrics
     */
    public function collectMetrics(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'cpu_load' => sys_getloadavg(),
            'memory' => [
                'usage' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
            ],
            'database' => $this->getDatabaseMetrics(),
            'application' => $this->getApplicationMetrics(),
        ];
    }

    /**
     * Get database-specific metrics
     */
    private function getDatabaseMetrics(): array
    {
        try {
            return [
                'users_count' => DB::table('users')->count(),
                'products_count' => DB::table('products')->count(),
                'sales_count' => DB::table('sales')->count(),
                'customers_count' => DB::table('customers')->count(),
                'sales_today' => DB::table('sales')
                    ->whereDate('created_at', today())
                    ->count(),
            ];
        } catch (Exception $e) {
            return ['error' => 'Unable to collect database metrics'];
        }
    }

    /**
     * Get application-specific metrics
     */
    private function getApplicationMetrics(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];
    }

    /**
     * Log health check results
     */
    public function logHealthCheck(): void
    {
        $health = $this->healthCheck();
        
        if ($health['overall_status'] === 'unhealthy') {
            Log::error('System health check failed', $health);
        } elseif (collect($health['checks'])->contains('status', 'warning')) {
            Log::warning('System health check has warnings', $health);
        } else {
            Log::info('System health check passed', [
                'status' => $health['overall_status'],
                'timestamp' => $health['timestamp']
            ]);
        }
    }
}

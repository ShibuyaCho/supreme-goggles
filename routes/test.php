<?php

use Illuminate\Support\Facades\Route;
use App\Services\MetrcService;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Secure Test Routes - ADMIN ONLY
|--------------------------------------------------------------------------
| These routes are for testing API connections and functionality
| RESTRICTED TO ADMIN USERS ONLY IN PRODUCTION
*/

// Middleware group for admin-only test endpoints
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
    Route::get('/metrc-test', function () {
        if (env('APP_ENV') === 'production' && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }

        try {
            $metrcService = app(MetrcService::class);
            
            // Test basic configuration
            $config = [
                'base_url' => config('services.metrc.base_url'),
                'user_key' => config('services.metrc.user_key') ? 'Set (***' . substr(config('services.metrc.user_key'), -4) . ')' : 'Not Set',
                'vendor_key' => config('services.metrc.vendor_key') ? 'Set (***' . substr(config('services.metrc.vendor_key'), -4) . ')' : 'Not Set',
                'username' => config('services.metrc.username') ? 'Set (' . config('services.metrc.username') . ')' : 'Not Set',
                'password' => config('services.metrc.password') ? 'Set (****)' : 'Not Set',
                'facility' => config('services.metrc.facility_license') ?: 'Not Set',
                'enabled' => config('services.metrc.enabled'),
            ];
            
            // Test API connection
            $connectionTest = $metrcService->testConnection();
            
            return response()->json([
                'status' => 'success',
                'message' => 'METRC Configuration Test',
                'config' => $config,
                'connection_test' => $connectionTest,
                'timestamp' => now()->toISOString(),
                'environment' => env('APP_ENV'),
                'authenticated_user' => auth()->user()->name
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'METRC test failed',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal error',
                'config' => [
                    'base_url' => config('services.metrc.base_url'),
                    'user_key' => config('services.metrc.user_key') ? 'Set' : 'Not Set',
                    'vendor_key' => config('services.metrc.vendor_key') ? 'Set' : 'Not Set',
                    'username' => config('services.metrc.username') ? 'Set' : 'Not Set',
                    'password' => config('services.metrc.password') ? 'Set' : 'Not Set',
                    'facility' => config('services.metrc.facility_license') ? 'Set' : 'Not Set',
                    'enabled' => config('services.metrc.enabled'),
                ],
                'timestamp' => now()->toISOString()
            ], 500);
        }
    });

    Route::get('/database-test', function () {
        if (env('APP_ENV') === 'production' && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }

        try {
            // Test database connection
            $pdo = \DB::connection()->getPdo();
            $databaseName = \DB::connection()->getDatabaseName();
            
            // Test if migrations table exists
            $migrationsTable = \DB::select("SHOW TABLES LIKE 'migrations'");
            $migrationsRun = !empty($migrationsTable);
            
            // Get list of tables (limit for security)
            $tables = \DB::select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array) $table)[0];
            }, $tables);
            
            // Get basic statistics
            $userCount = \DB::table('users')->count();
            $productCount = \DB::table('products')->count();
            $customerCount = \DB::table('customers')->count();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Database connection successful',
                'database' => $databaseName,
                'migrations_run' => $migrationsRun,
                'tables' => $tableNames,
                'table_count' => count($tableNames),
                'statistics' => [
                    'users' => $userCount,
                    'products' => $productCount,
                    'customers' => $customerCount
                ],
                'environment' => env('APP_ENV'),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Database connection error',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    });

    Route::get('/env-test', function () {
        if (env('APP_ENV') === 'production' && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.',
            ], 403);
        }

        // Only show safe environment variables
        $safeEnvVars = [
            'APP_ENV' => env('APP_ENV'),
            'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
            'DB_CONNECTION' => env('DB_CONNECTION'),
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'METRC_ENABLED' => env('METRC_ENABLED') ? 'true' : 'false',
            'METRC_BASE_URL' => env('METRC_BASE_URL'),
            'METRC_USER_KEY' => env('METRC_USER_KEY') ? 'Set (***' . substr(env('METRC_USER_KEY'), -4) . ')' : 'Not Set',
            'METRC_VENDOR_KEY' => env('METRC_VENDOR_KEY') ? 'Set (***' . substr(env('METRC_VENDOR_KEY'), -4) . ')' : 'Not Set',
            'METRC_USERNAME' => env('METRC_USERNAME') ? 'Set (' . env('METRC_USERNAME') . ')' : 'Not Set',
            'METRC_PASSWORD' => env('METRC_PASSWORD') ? 'Set (****)' : 'Not Set',
            'METRC_FACILITY' => env('METRC_FACILITY') ? 'Set (' . env('METRC_FACILITY') . ')' : 'Not Set',
        ];
        
        return response()->json([
            'status' => 'success',
            'message' => 'Environment Variables Status (Safe View)',
            'variables' => $safeEnvVars,
            'environment' => env('APP_ENV'),
            'authenticated_user' => auth()->user()->name,
            'timestamp' => now()->toISOString()
        ]);
    });

    // System health check endpoint
    Route::get('/health-check', function () {
        $checks = [
            'database' => false,
            'metrc' => false,
            'storage' => false,
            'cache' => false
        ];

        try {
            // Database check
            \DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (\Exception $e) {
            // Database failed
        }

        try {
            // METRC check
            $metrcService = app(MetrcService::class);
            $metrcTest = $metrcService->testConnection();
            $checks['metrc'] = $metrcTest['success'] ?? false;
        } catch (\Exception $e) {
            // METRC failed
        }

        try {
            // Storage check
            $testFile = storage_path('logs/health-check.tmp');
            file_put_contents($testFile, 'test');
            $checks['storage'] = file_exists($testFile);
            @unlink($testFile);
        } catch (\Exception $e) {
            // Storage failed
        }

        try {
            // Cache check
            cache()->put('health-check', 'test', 10);
            $checks['cache'] = cache()->get('health-check') === 'test';
            cache()->forget('health-check');
        } catch (\Exception $e) {
            // Cache failed
        }

        $overallHealth = array_reduce($checks, function($carry, $check) {
            return $carry && $check;
        }, true);

        return response()->json([
            'status' => $overallHealth ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
            'environment' => env('APP_ENV')
        ], $overallHealth ? 200 : 503);
    });
});

// Public health endpoint (limited info)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

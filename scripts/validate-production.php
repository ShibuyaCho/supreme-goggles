<?php
/**
 * Cannabis POS - Production Environment Validation
 * 
 * This script validates that the production environment is properly configured
 * and secure before going live.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class ProductionValidator
{
    private $errors = [];
    private $warnings = [];
    private $passed = [];

    public function validate()
    {
        echo "🔍 Cannabis POS Production Validation\n";
        echo "=====================================\n\n";

        $this->validateEnvironment();
        $this->validateDatabase();
        $this->validateSecurity();
        $this->validateMetrc();
        $this->validateFilePermissions();
        $this->validateConfiguration();

        $this->printResults();
    }

    private function validateEnvironment()
    {
        echo "📋 Environment Configuration\n";
        echo "----------------------------\n";

        // Check APP_ENV
        if (env('APP_ENV') !== 'production') {
            $this->errors[] = "APP_ENV must be set to 'production'";
        } else {
            $this->passed[] = "APP_ENV correctly set to production";
        }

        // Check APP_DEBUG
        if (env('APP_DEBUG') === true || env('APP_DEBUG') === 'true') {
            $this->errors[] = "APP_DEBUG must be false in production";
        } else {
            $this->passed[] = "APP_DEBUG correctly disabled";
        }

        // Check APP_KEY
        if (!env('APP_KEY')) {
            $this->errors[] = "APP_KEY not set - run 'php artisan key:generate'";
        } else {
            $this->passed[] = "APP_KEY is configured";
        }

        // Check APP_URL
        if (!env('APP_URL') || env('APP_URL') === 'http://localhost') {
            $this->warnings[] = "APP_URL should be set to your production domain";
        } else {
            $this->passed[] = "APP_URL is configured";
        }

        echo "\n";
    }

    private function validateDatabase()
    {
        echo "🗄️  Database Configuration\n";
        echo "--------------------------\n";

        try {
            // Test database connection
            $pdo = DB::connection()->getPdo();
            $this->passed[] = "Database connection successful";

            // Check database name
            $dbName = DB::connection()->getDatabaseName();
            if (strpos($dbName, 'test') !== false || strpos($dbName, 'dev') !== false) {
                $this->warnings[] = "Database name contains 'test' or 'dev' - ensure this is correct for production";
            }

            // Check if migrations are run
            $tables = DB::select('SHOW TABLES');
            if (count($tables) < 5) {
                $this->errors[] = "Database appears empty - run 'php artisan migrate'";
            } else {
                $this->passed[] = "Database tables exist";
            }

            // Check for users
            $userCount = DB::table('users')->count();
            if ($userCount === 0) {
                $this->errors[] = "No users in database - run 'php artisan db:seed --class=ProductionUserSeeder'";
            } else {
                $this->passed[] = "Users exist in database";
            }

            // Check for demo users
            $demoUsers = DB::table('users')->where('email', 'like', '%@cannabis-pos.local')->count();
            if ($demoUsers > 0) {
                $this->errors[] = "Demo users found - remove before production";
            }

        } catch (Exception $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }

        echo "\n";
    }

    private function validateSecurity()
    {
        echo "🔐 Security Configuration\n";
        echo "------------------------\n";

        // Check .env file permissions
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $perms = substr(sprintf('%o', fileperms($envPath)), -4);
            if ($perms !== '0600') {
                $this->warnings[] = ".env file permissions should be 600 (currently {$perms})";
            } else {
                $this->passed[] = ".env file has secure permissions";
            }
        }

        // Check for demo routes
        if (file_exists(base_path('routes/test.php'))) {
            $this->warnings[] = "Test routes file exists - ensure it's properly secured";
        }

        // Check session configuration
        if (config('session.secure') !== true) {
            $this->warnings[] = "SESSION_SECURE should be true for HTTPS";
        }

        // Check for HTTPS
        if (!request()->isSecure() && env('APP_ENV') === 'production') {
            $this->errors[] = "HTTPS not detected - ensure SSL is properly configured";
        } else {
            $this->passed[] = "HTTPS is configured";
        }

        // Check CSRF token
        if (!config('app.key')) {
            $this->errors[] = "Application key not set";
        }

        echo "\n";
    }

    private function validateMetrc()
    {
        echo "🌿 METRC Integration\n";
        echo "-------------------\n";

        // Check METRC configuration
        $metrcEnabled = config('services.metrc.enabled');
        if (!$metrcEnabled) {
            $this->warnings[] = "METRC integration is disabled";
        }

        $requiredMetrcVars = [
            'METRC_USER_KEY',
            'METRC_VENDOR_KEY',
            'METRC_USERNAME',
            'METRC_PASSWORD',
            'METRC_FACILITY'
        ];

        foreach ($requiredMetrcVars as $var) {
            if (!env($var)) {
                $this->errors[] = "{$var} not configured";
            }
        }

        if (!env('METRC_FACILITY')) {
            $this->errors[] = "METRC_FACILITY (license number) must be set";
        }

        // Test METRC connection
        try {
            $metrcService = app(\App\Services\MetrcService::class);
            if (method_exists($metrcService, 'testConnection')) {
                $result = $metrcService->testConnection();
                if ($result['success'] ?? false) {
                    $this->passed[] = "METRC connection test successful";
                } else {
                    $this->warnings[] = "METRC connection test failed - check credentials";
                }
            }
        } catch (Exception $e) {
            $this->warnings[] = "METRC service error: " . $e->getMessage();
        }

        echo "\n";
    }

    private function validateFilePermissions()
    {
        echo "📁 File Permissions\n";
        echo "------------------\n";

        $directories = [
            'storage' => 755,
            'storage/logs' => 755,
            'bootstrap/cache' => 755
        ];

        foreach ($directories as $dir => $expectedPerm) {
            $path = base_path($dir);
            if (is_dir($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -3);
                if ($perms < $expectedPerm) {
                    $this->warnings[] = "{$dir} permissions are {$perms}, should be at least {$expectedPerm}";
                } else {
                    $this->passed[] = "{$dir} has correct permissions";
                }
            }
        }

        echo "\n";
    }

    private function validateConfiguration()
    {
        echo "⚙️  Application Configuration\n";
        echo "-----------------------------\n";

        // Check for demo emails
        $adminEmail = env('ADMIN_EMAIL');
        if (!$adminEmail || strpos($adminEmail, 'yourdomain.com') !== false) {
            $this->errors[] = "ADMIN_EMAIL must be set to a real email address";
        }

        // Check log level
        if (env('LOG_LEVEL') !== 'error') {
            $this->warnings[] = "LOG_LEVEL should be 'error' in production";
        }

        // Check cache driver
        if (config('cache.default') === 'array') {
            $this->warnings[] = "Cache driver 'array' is not persistent - consider 'file' or 'redis'";
        }

        // Check queue driver
        if (config('queue.default') === 'sync') {
            $this->warnings[] = "Queue driver 'sync' processes jobs immediately - consider 'database' or 'redis'";
        }

        echo "\n";
    }

    private function printResults()
    {
        echo "📊 Validation Results\n";
        echo "====================\n\n";

        if (!empty($this->passed)) {
            echo "✅ PASSED (" . count($this->passed) . ")\n";
            foreach ($this->passed as $item) {
                echo "  ✓ {$item}\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS (" . count($this->warnings) . ")\n";
            foreach ($this->warnings as $item) {
                echo "  ⚠ {$item}\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
            echo "❌ ERRORS (" . count($this->errors) . ")\n";
            foreach ($this->errors as $item) {
                echo "  ✗ {$item}\n";
            }
            echo "\n";
        }

        // Final verdict
        if (empty($this->errors)) {
            if (empty($this->warnings)) {
                echo "🎉 PRODUCTION READY!\n";
                echo "Your Cannabis POS system is properly configured for production.\n";
            } else {
                echo "🟡 MOSTLY READY\n";
                echo "Your system can go to production, but address warnings for optimal security.\n";
            }
        } else {
            echo "🔴 NOT READY FOR PRODUCTION\n";
            echo "Critical errors must be fixed before going live.\n";
        }

        echo "\n";
        echo "Summary: " . count($this->passed) . " passed, " . 
             count($this->warnings) . " warnings, " . 
             count($this->errors) . " errors\n";
    }
}

// Run validation
$validator = new ProductionValidator();
$validator->validate();

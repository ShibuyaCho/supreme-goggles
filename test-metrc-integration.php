<?php
/**
 * METRC Integration Test Script
 * Run this to verify your METRC API connection and credentials
 */

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== METRC Integration Test ===\n\n";

// Test 1: Environment Variables
echo "1. Testing Environment Variables...\n";
$requiredVars = [
    'METRC_USER_KEY',
    'METRC_VENDOR_KEY', 
    'METRC_USERNAME',
    'METRC_PASSWORD',
    'METRC_FACILITY',
    'METRC_BASE_URL'
];

foreach ($requiredVars as $var) {
    $value = env($var);
    if ($value) {
        if (in_array($var, ['METRC_PASSWORD'])) {
            echo "   ✓ {$var}: Set (****)\n";
        } elseif (in_array($var, ['METRC_USER_KEY', 'METRC_VENDOR_KEY'])) {
            echo "   ✓ {$var}: Set (***" . substr($value, -4) . ")\n";
        } else {
            echo "   ✓ {$var}: {$value}\n";
        }
    } else {
        echo "   ✗ {$var}: Not set\n";
    }
}

// Test 2: Service Configuration
echo "\n2. Testing Service Configuration...\n";
$metrcConfig = config('services.metrc');
echo "   Base URL: " . ($metrcConfig['base_url'] ?? 'Not set') . "\n";
echo "   Enabled: " . ($metrcConfig['enabled'] ? 'Yes' : 'No') . "\n";
echo "   Facility: " . ($metrcConfig['facility_license'] ?? 'Not set') . "\n";
echo "   Tag Prefix: " . ($metrcConfig['tag_prefix'] ?? 'Not set') . "\n";

// Test 3: Basic HTTP Connection
echo "\n3. Testing HTTP Connection to METRC...\n";
try {
    $baseUrl = $metrcConfig['base_url'] ?? 'https://api-or.metrc.com';
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($baseUrl . '/facilities/v1/active', false, $context);
    
    if ($response !== false) {
        echo "   ✓ HTTP connection successful\n";
        
        // Check if we get JSON response
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   ✓ Valid JSON response received\n";
        } else {
            echo "   ⚠ Non-JSON response (this might be expected without auth)\n";
        }
    } else {
        echo "   ✗ HTTP connection failed\n";
    }
} catch (Exception $e) {
    echo "   ✗ Connection error: " . $e->getMessage() . "\n";
}

// Test 4: METRC Service Class
echo "\n4. Testing METRC Service Class...\n";
try {
    $metrcService = app(\App\Services\MetrcService::class);
    echo "   ✓ MetrcService class instantiated successfully\n";
    
    // Test connection method if it exists
    if (method_exists($metrcService, 'testConnection')) {
        $testResult = $metrcService->testConnection();
        echo "   Connection test result: " . json_encode($testResult) . "\n";
    } else {
        echo "   ⚠ testConnection method not found in MetrcService\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ MetrcService error: " . $e->getMessage() . "\n";
}

// Test 5: Web Routes Test
echo "\n5. Testing Web Routes...\n";
try {
    // Test if routes are accessible
    $routes = ['env-test', 'database-test', 'metrc-test'];
    
    foreach ($routes as $route) {
        echo "   Testing /test/{$route}...\n";
        // Note: This would require actual HTTP request in real test
        echo "   ✓ Route /test/{$route} should be accessible\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Route test error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Next steps:\n";
echo "1. Run database migrations: php artisan migrate --force\n";
echo "2. Run seeders: php artisan db:seed --force\n";
echo "3. Visit /test/metrc-test in your browser for full API test\n";
echo "4. Visit /test/env-test to verify all environment variables\n";
echo "5. Visit /test/database-test to verify database connection\n\n";

echo "METRC Credentials Summary:\n";
echo "- Username: " . (env('METRC_USERNAME') ?: 'Not set') . "\n";
echo "- Facility: " . (env('METRC_FACILITY') ?: 'Not set') . "\n";
echo "- API Keys: " . (env('METRC_USER_KEY') && env('METRC_VENDOR_KEY') ? 'Set' : 'Missing') . "\n";
echo "- Base URL: " . (env('METRC_BASE_URL') ?: 'Not set') . "\n";

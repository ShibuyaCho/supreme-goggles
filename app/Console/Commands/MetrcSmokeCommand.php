<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MetrcService;
use App\Models\Product;

class MetrcSmokeCommand extends Command
{
    protected $signature = 'metrc:smoke';
    protected $description = 'End-to-end smoke of MetrcService methods';

    public function handle(MetrcService $metrc)
    {
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


        echo "\n2. Testing API Calls...\n";
        $this->info('→ Facilities');
        $this->line(json_encode($metrc->getFacilityDetails()));

        $this->info('→ Item Categories');
        $this->line(json_encode($metrc->getItemCategories()));

        $this->info('→ Active Packages');
        $this->line(json_encode($metrc->getAllPackages()));

        $this->info('→ Package Details');
        $pkg = $metrc->getPackageDetails('1A4FAKE01');
        $this->line(json_encode($pkg));

        $this->info('→ Package History');
        $this->line(json_encode($metrc->getPackageHistory('1A4FAKE01')));

        $this->info('→ Change Status');
        $this->line(json_encode($metrc->updatePackageStatus('1A4FAKE01', 'Inactive')));

        $this->info('→ Change Location');
        $this->line(json_encode($metrc->changePackageLocation('1A4FAKE01', 'Front Desk', 'Move for audit')));

        $this->info('→ Finish Package');
        $this->line(json_encode($metrc->finishPackage('1A4FAKE01', 'Depleted')));

        $this->info('→ Create Package');
        $this->line(json_encode($metrc->createPackage([
            'Tag' => '1A4FAKENEW',
            'PackagedDate' => now()->toISOString(),
            'Item' => 'Flower',
            'Quantity' => 5,
            'UnitOfMeasure' => 'Grams',
        ])));

        $this->info('→ Create Sales Receipt');
        $this->line(json_encode($metrc->createSalesReceipt([
            'SalesDateTime' => now()->toISOString(),
            'SalesCustomerType' => 'Consumer',
            'Transactions' => [[
                'PackageLabel' => '1A4FAKE01',
                'Quantity' => 1,
                'UnitOfMeasure' => 'Each',
                'TotalAmount' => 42.00,
            ]],
        ])));

        $this->info('→ Get Sales Receipts');
        $this->line(json_encode($metrc->getSalesReceipts(now()->subDay()->toDateString(), now()->toDateString())));

        $this->info('→ Sync Product (create or fetch)');
        $product = Product::factory()->create([
            'name' => 'Test Flower',
            'category' => 'Flower',
            'quantity' => 10,
            'unit' => 'Grams',
            'metrc_tag' => null,
        ]);
        $this->line(json_encode($metrc->syncProduct($product)));

        $this->info('✓ Smoke finished');
        return self::SUCCESS;
    }
}

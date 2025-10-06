<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\MetrcService;
use App\Models\Product;

class MetrcSyncInventory extends Command
{
    protected $signature = 'metrc:sync-inventory {--full : Perform a full active packages import first} {--window-days=7 : Window size in days for incremental sync}';
    protected $description = 'Sync inventory with Oregon METRC: active/inactive packages, transfers, and lab enrichment';

    public function handle(MetrcService $metrc)
    {
        if (!$metrc->isConfigured()) {
            $this->error('METRC is not configured. Set METRC_USER_KEY, METRC_VENDOR_KEY, METRC_FACILITY, METRC_BASE_URL.');
            return 1;
        }

        $start = cache('metrc_sync_cursor') ? now()->parse(cache('metrc_sync_cursor')) : now()->subDays(30);
        $end = now();
        $windowDays = (int) $this->option('window-days');

        $summary = [
            'windows_processed' => 0,
            'active_processed' => 0,
            'inactive_processed' => 0,
            'outgoing_transfers' => 0,
            'deliveries_processed' => 0,
            'transfer_packages_processed' => 0,
            'products_created' => 0,
            'products_updated' => 0,
            'products_deactivated' => 0,
        ];

        try {
            if ($this->option('full') || !cache('metrc_sync_cursor')) {
                $this->info('Performing one-time full active packages import…');
                $allActive = (array) $metrc->getAllPackages();
                $this->importPackages($metrc, $allActive, $summary);
                cache(['metrc_sync_cursor' => now()->toIso8601String()], now()->addDays(7));
            }

            $cursor = $start->clone();
            while ($cursor->lt($end)) {
                $windowStart = $cursor->clone();
                $windowEnd = $cursor->clone()->addDays($windowDays);
                if ($windowEnd->gt($end)) { $windowEnd = $end->clone(); }
                $this->line("Window: {$windowStart->toIso8601String()} → {$windowEnd->toIso8601String()}");

                // Active packages (windowed)
                $active = (array) $metrc->getAllPackages($windowStart->toIso8601String(), $windowEnd->toIso8601String());
                $this->importPackages($metrc, $active, $summary);

                // Inactive packages → set quantity 0
                $inactive = (array) $metrc->getInactivePackages($windowStart->toIso8601String(), $windowEnd->toIso8601String());
                foreach ($inactive as $pkg) {
                    $label = $pkg['Label'] ?? $pkg['label'] ?? null;
                    if (!$label) { continue; }
                    $p = Product::where('metrc_tag', $label)->first();
                    if ($p && $p->quantity > 0) { $p->update(['quantity' => 0]); $summary['products_deactivated']++; }
                    $summary['inactive_processed']++;
                }

                $summary['windows_processed']++;
                $cursor = $windowEnd->clone();
                cache(['metrc_sync_cursor' => $cursor->toIso8601String()], now()->addDays(7));
            }

            $this->info('METRC inventory sync complete');
            $this->table(array_keys($summary), [array_values($summary)]);
            return 0;
        } catch (\Throwable $e) {
            Log::error('METRC sync failed', ['error' => $e->getMessage()]);
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function importPackages(MetrcService $metrc, array $packages, array &$summary): void
    {
        foreach ($packages as $pkg) {
            $qty = (int)($pkg['Quantity'] ?? $pkg['quantity'] ?? 0);
            if ($qty <= 0) { continue; }
            $label = $pkg['Label'] ?? $pkg['label'] ?? null; if (!$label) { continue; }

            $item = $pkg['Item'] ?? [];
            $itemName = is_array($item) ? ($item['Name'] ?? $item['name'] ?? null) : null;
            $category = is_array($item) ? ($item['Category'] ?? $item['category'] ?? null) : ($pkg['Category'] ?? $pkg['category'] ?? null);
            $uom = $pkg['UnitOfMeasureName'] ?? $pkg['UnitOfMeasure'] ?? $pkg['unitOfMeasure'] ?? $pkg['UnitOfMeasureAbbreviation'] ?? $pkg['unit_of_measure'] ?? '';
            $packagedDate = $pkg['PackagedDate'] ?? $pkg['packagedDate'] ?? null;
            $expDate = $pkg['ExpirationDate'] ?? $pkg['expirationDate'] ?? null;
            $vendor = $pkg['SourceFacilityLicenseNumber'] ?? $pkg['SourceFacility'] ?? null;

            $data = [
                'name' => $itemName ?: ($pkg['ProductName'] ?? $pkg['productName'] ?? ('METRC Package ' . $label)),
                'category' => $category ?: 'Unknown',
                'price' => 0,
                'cost' => 0,
                'sku' => $label,
                'weight' => $uom ?: 'Units',
                'unit' => $uom ?: 'Each',
                'room' => 'Inventory',
                'supplier' => $vendor ?: 'METRC',
                'vendor' => $vendor ?: 'METRC',
                'packaged_date' => $packagedDate ? date('Y-m-d', strtotime($packagedDate)) : null,
                'expiration_date' => $expDate ? date('Y-m-d', strtotime($expDate)) : null,
                'metrc_tag' => $label,
                'quantity' => $qty,
            ];

            // Lab enrichment (best-effort)
            $pkgId = $pkg['Id'] ?? $pkg['PackageId'] ?? null;
            if ($pkgId) {
                try {
                    $labResp = $metrc->getLabTestResults($pkgId, null, 20);
                    if ($labResp) {
                        $parsed = $metrc->parseLabResults($labResp);
                        $data = array_merge($data, array_filter($parsed, fn($v) => $v !== null));
                    }
                } catch (\Throwable $e) {}
            }

            $existing = Product::where('metrc_tag', $label)->first();
            if ($existing) { $existing->fill($data)->save(); $summary['products_updated']++; }
            else { Product::create($data); $summary['products_created']++; }
            $summary['active_processed']++;
        }
    }
}

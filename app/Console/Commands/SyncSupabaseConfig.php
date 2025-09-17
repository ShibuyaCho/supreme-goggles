<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncSupabaseConfig extends Command
{
    protected $signature = 'pos:reconcile {--direction=pull : pull|push|two-way}';
    protected $description = 'Reconcile POS settings and price tiers between Supabase and local DB';

    public function handle(): int
    {
        $dir = strtolower((string)$this->option('direction'));
        if (!in_array($dir, ['pull','push','two-way'], true)) {
            $dir = 'pull';
        }

        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        if (!$supabaseUrl || !$supabaseKey) {
            $this->error('Supabase is not configured.');
            return self::FAILURE;
        }

        $headers = [
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
        ];

        // Reconcile settings (pos_settings)
        try {
            $this->info('Reconciling pos_settings...');
            $stores = ['default','defaultstore'];
            foreach ($stores as $storeId) {
                $r = Http::withHeaders($headers)->get($supabaseUrl . '/rest/v1/pos_settings', [
                    'id' => 'eq.' . $storeId,
                    'select' => '*',
                ]);
                $remote = null;
                if ($r->ok()) {
                    $arr = $r->json();
                    $remote = (is_array($arr) && isset($arr[0])) ? $arr[0] : null;
                }
                $local = DB::table('pos_settings')->where('id', $storeId)->first();

                if ($dir === 'pull' || $dir === 'two-way') {
                    if ($remote && is_array($remote) && isset($remote['settings']) && is_array($remote['settings'])) {
                        DB::table('pos_settings')->updateOrInsert(
                            ['id' => $storeId],
                            ['settings' => json_encode($remote['settings']), 'updated_at' => now()]
                        );
                        Log::info('Pulled settings from Supabase', ['store' => $storeId]);
                    }
                }
                if ($dir === 'push' || $dir === 'two-way') {
                    if ($local) {
                        $body = [[
                            'id' => $storeId,
                            'settings' => json_decode($local->settings, true) ?: [],
                            'updated_at' => now()->toIso8601String(),
                        ]];
                        Http::withHeaders($headers + ['Prefer' => 'resolution=merge-duplicates,return=representation'])
                            ->post($supabaseUrl . '/rest/v1/pos_settings?on_conflict=id', $body);
                        Log::info('Pushed settings to Supabase', ['store' => $storeId]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Settings reconcile failed', ['error' => $e->getMessage()]);
        }

        // Reconcile price_tiers (basic: pull-only if two-way not desired)
        try {
            $this->info('Reconciling price_tiers...');
            $r = Http::withHeaders($headers)->get($supabaseUrl . '/rest/v1/price_tiers', [ 'select' => '*' ]);
            if ($r->ok()) {
                $rows = $r->json() ?: [];
                foreach ($rows as $row) {
                    // Map minimal fields present locally
                    $id = $row['id'] ?? null;
                    if ($id === null) continue;
                    $payload = [
                        'name' => $row['name'] ?? 'Tier',
                        'description' => $row['description'] ?? null,
                        'minimum_quantity' => $row['minimum_quantity'] ?? ($row['min_quantity'] ?? null),
                        'discount_percentage' => $row['discount_percentage'] ?? ($row['discount'] ?? 0),
                        'applicable_categories' => $row['applicable_categories'] ?? [],
                        'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
                        'updated_at' => now(),
                    ];
                    // Upsert into local Eloquent table via query builder
                    DB::table('price_tiers')->updateOrInsert(['id' => $id], $payload);
                }
                Log::info('Pulled price tiers from Supabase', ['count' => count($rows)]);
            }
        } catch (\Throwable $e) {
            Log::warning('Price tiers reconcile failed', ['error' => $e->getMessage()]);
        }

        $this->info('Reconciliation complete.');
        return self::SUCCESS;
    }
}

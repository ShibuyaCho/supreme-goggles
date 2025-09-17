<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PriceTier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PriceTiersController extends Controller
{
    public function index()
    {
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        $tiers = [];

        if ($supabaseUrl && $supabaseKey) {
            try {
                $resp = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get($supabaseUrl . '/rest/v1/price_tiers', [
                    'select' => '*',
                    'order' => 'updated_at.desc'
                ]);
                if ($resp->ok()) {
                    $rows = $resp->json() ?: [];
                    foreach ($rows as $r) {
                        $name = (string)($r['name'] ?? 'Tier');
                        $isActive = isset($r['is_active']) ? (bool)$r['is_active'] : (strtolower((string)($r['status'] ?? 'active')) === 'active');
                        $type = (string)($r['type'] ?? 'retail');
                        $cust = (string)($r['customer_type'] ?? 'recreational');
                        $discount = null;
                        if (isset($r['discount'])) {
                            $discount = (float)$r['discount'];
                        } elseif (isset($r['discount_percentage'])) {
                            $discount = (float)$r['discount_percentage'];
                        } elseif (($r['type'] ?? '') === 'percentage' && isset($r['adjustment_value'])) {
                            $discount = (float)$r['adjustment_value'];
                        } else {
                            $discount = 0.0;
                        }
                        $minQty = null;
                        if (isset($r['min_quantity'])) $minQty = $r['min_quantity'];
                        elseif (isset($r['minimum_quantity'])) $minQty = $r['minimum_quantity'];
                        $minQty = is_null($minQty) ? null : (is_numeric($minQty) ? 0 + $minQty : null);

                        $schedule = null;
                        $vf = $r['valid_from'] ?? null; $vu = $r['valid_until'] ?? null;
                        if ($vf || $vu) {
                            $schedule = [
                                'start_date' => $vf,
                                'end_date' => $vu,
                            ];
                        }
                        $productCount = (int)($r['product_count'] ?? 0);

                        $tiers[] = [
                            'id' => $r['id'] ?? null,
                            'name' => $name,
                            'description' => $r['description'] ?? null,
                            'status' => $isActive ? 'active' : 'inactive',
                            'type' => $type,
                            'customer_type' => $cust,
                            'discount' => $discount,
                            'min_quantity' => $minQty,
                            'min_amount' => isset($r['min_amount']) ? (float)$r['min_amount'] : null,
                            'product_count' => $productCount,
                            'schedule' => $schedule,
                            'updated_at' => $r['updated_at'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to load price tiers from Supabase', ['error' => $e->getMessage()]);
            }
        }

        // Compute lightweight stats for header cards
        $activeCount = 0; $custSet = [];
        $recentUpdates = 0; $now = time();
        foreach ($tiers as $t) {
            if (($t['status'] ?? 'inactive') === 'active') $activeCount++;
            $custSet[$t['customer_type'] ?? 'recreational'] = true;
            $ts = isset($t['updated_at']) ? strtotime((string)$t['updated_at']) : null;
            if ($ts && ($now - $ts) <= 30 * 24 * 3600) $recentUpdates++;
        }
        $stats = [
            'active_tiers' => $activeCount,
            'avg_margin' => 0.0,
            'recent_updates' => $recentUpdates ?: count($tiers),
            'customer_types' => count($custSet),
        ];

        $detailed = $tiers; // Same dataset for detailed table for now

        \Illuminate\Support\Facades\Log::info('Price Tiers index loaded', ['count' => count($tiers)]);

        return view('price-tiers.index', [
            'price_tiers' => $tiers,
            'detailed_tiers' => $detailed,
            'stats' => $stats,
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'minimum_quantity' => 'required|integer|min:1',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'applicable_categories' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $priceTier = PriceTier::create($request->all());
        
        return response()->json([
            'message' => 'Price tier created successfully',
            'price_tier' => $priceTier
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $priceTier = PriceTier::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'minimum_quantity' => 'required|integer|min:1',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'applicable_categories' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $priceTier->update($request->all());
        
        return response()->json([
            'message' => 'Price tier updated successfully',
            'price_tier' => $priceTier
        ]);
    }
    
    public function destroy($id)
    {
        $priceTier = PriceTier::findOrFail($id);
        $priceTier->delete();
        
        return response()->json(['message' => 'Price tier deleted successfully']);
    }
}

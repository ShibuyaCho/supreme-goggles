<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class DealsController extends Controller
{
    public function index(Request $request)
    {
        $deals = null;
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        if ($supabaseUrl && $supabaseKey) {
            try {
                $resp = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/deals', [
                    'select' => '*',
                    'order' => 'created_at.desc'
                ]);
                if ($resp->ok()) {
                    $rows = $resp->json();
                    $supabaseDeals = collect(is_array($rows) ? $rows : []);

                    // Always also load local deals and merge any that aren't present in Supabase
                    $localDeals = Deal::orderBy('created_at','desc')->get();
                    $merged = collect([]);
                    // Normalize keys and avoid id collisions
                    $supabaseByKey = $supabaseDeals->keyBy(function($d){
                        $id = is_array($d) ? ($d['id'] ?? null) : (is_object($d) ? ($d->id ?? null) : null);
                        return 'supa:' . (string)$id;
                    });
                    $merged = $merged->merge($supabaseByKey->values());

                    foreach ($localDeals as $d) {
                        $key = 'local:' . (string)$d->id;
                        // If a Supabase row with same numeric id exists, keep Supabase and skip to avoid duplicates
                        if ($supabaseDeals->contains(function($sd) use ($d){
                            $sid = is_array($sd) ? ($sd['id'] ?? null) : (is_object($sd) ? ($sd->id ?? null) : null);
                            return (string)$sid === (string)$d->id;
                        })) {
                            continue;
                        }
                        $merged->push($d);
                    }

                    $deals = $merged;

                    try {
                        if ($supabaseDeals->count() === 0) {
                            // If Supabase is empty, push all local deals up
                            if ($localDeals->count() > 0) {
                                $payloads = $localDeals->map(function($d){
                                    $arr = $d->toArray();
                                    if (isset($arr['applicable_categories']) && is_string($arr['applicable_categories'])) {
                                        $dec = json_decode($arr['applicable_categories'], true);
                                        if (json_last_error() === JSON_ERROR_NONE) $arr['applicable_categories'] = $dec;
                                    }
                                    if (isset($arr['specific_items']) && is_string($arr['specific_items'])) {
                                        $dec = json_decode($arr['specific_items'], true);
                                        if (json_last_error() === JSON_ERROR_NONE) $arr['specific_items'] = $dec;
                                    }
                                    if (isset($arr['active_days']) && is_string($arr['active_days'])) {
                                        $dec = json_decode($arr['active_days'], true);
                                        if (json_last_error() === JSON_ERROR_NONE) $arr['active_days'] = $dec;
                                    }
                                    return $arr;
                                })->values()->all();
                                Http::withHeaders([
                                    'apikey' => $supabaseKey,
                                    'Authorization' => 'Bearer ' . $supabaseKey,
                                    'Accept' => 'application/json',
                                    'Prefer' => 'return=representation'
                                ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/deals', $payloads);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore sync failure
                    }
                }
            } catch (\Throwable $e) {
                $deals = null;
            }
        }
        if ($deals === null) {
            $deals = Deal::orderBy('created_at', 'desc')->get();
        }

        // Load METRC categories with safe fallback and ensure 'Infused' always included
        $categories = [];
        try {
            $categories = app(\App\Services\MetrcService::class)->getItemCategories();
            if (is_array($categories) && isset($categories[0]) && is_array($categories[0]) && (isset($categories[0]['Name']) || isset($categories[0]['name']))) {
                $categories = collect($categories)->map(fn($c) => $c['Name'] ?? $c['name'])->values()->all();
            }
        } catch (\Throwable $e) {
            $categories = [
                'Flower','Pre-Rolls','Concentrates','Extracts','Edibles','Topicals','Tinctures','Vape Cartridges','Vape Pens','Inhalable Cannabinoids','Clones','Immature Plants','Seeds','Shake/Trim','Kief','Accessories'
            ];
        }
        // Guarantee 'Infused' exists on both demo and live
        try {
            $categories = collect(is_array($categories) ? $categories : [])
                ->filter(fn($c) => is_string($c) && $c !== '')
                ->push('Infused')
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) { /* ignore */ }

        if ($request->wantsJson() || $request->expectsJson()) {
            $list = $deals instanceof \Illuminate\Support\Collection ? $deals->values()->all() : (is_array($deals) ? $deals : []);
            if ($deals instanceof \Illuminate\Support\Collection) {
                $list = $deals->map(fn($d) => $this->formatDealForResponse($d))->values()->all();
            }
            return response()->json([
                'success' => true,
                'deals' => $list,
                'categories' => $categories,
            ]);
        }

        // For Blade view, normalize each deal to consistent array structure as well
        $normalizedDeals = $deals instanceof \Illuminate\Support\Collection
            ? $deals->map(fn($d) => $this->formatDealForResponse($d))->values()->all()
            : (is_array($deals) ? array_map(fn($d) => $this->formatDealForResponse($d), $deals) : []);

        return view('deals.index', ['deals' => $normalizedDeals, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed,fixed_amount,bogo,bulk',
            'value' => 'required|numeric|min:0',
            'frequency' => 'required|in:always,daily,weekly,monthly,custom',
            'day_of_week' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'applicable_categories' => 'nullable|array',
            'specific_items' => 'nullable|array',
            'category_discounts' => 'nullable|array',
            'item_discounts' => 'nullable|array',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'minimum_purchase_type' => 'nullable|in:dollars,grams',
            'max_uses' => 'nullable|integer|min:1',
            'email_customers' => 'boolean',
            'loyalty_only' => 'boolean',
            'medical_only' => 'boolean',
            'is_active' => 'boolean',
            'active_days' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dealData = $request->all();
            $dealData['current_uses'] = 0;

            // Normalize optional dates
            foreach (['start_date','end_date'] as $dk) {
                if (isset($dealData[$dk]) && (!$dealData[$dk] || $dealData[$dk] === '')) {
                    $dealData[$dk] = null;
                }
            }

            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                try {
                    $payload = $dealData;
                    if (isset($payload['applicable_categories']) && is_string($payload['applicable_categories'])) {
                        $payload['applicable_categories'] = json_decode($payload['applicable_categories'], true);
                    }
                    if (isset($payload['specific_items']) && is_string($payload['specific_items'])) {
                        $payload['specific_items'] = json_decode($payload['specific_items'], true);
                    }
                    if (isset($payload['active_days']) && is_string($payload['active_days'])) {
                        $payload['active_days'] = json_decode($payload['active_days'], true);
                    }
                    if (isset($payload['category_discounts']) && is_string($payload['category_discounts'])) {
                        $payload['category_discounts'] = json_decode($payload['category_discounts'], true);
                    }
                    if (isset($payload['item_discounts']) && is_string($payload['item_discounts'])) {
                        $payload['item_discounts'] = json_decode($payload['item_discounts'], true);
                    }
                    $resp = Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Accept' => 'application/json',
                        'Prefer' => 'return=representation'
                    ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/deals', [$payload]);
                    if ($resp->successful()) {
                        $rows = $resp->json();
                        $row = is_array($rows) && isset($rows[0]) ? $rows[0] : $rows;
                        // Mirror to local DB for persistence across sessions and offline
                        try { $this->upsertLocalDealFromSupabaseRow($row); } catch (\Throwable $e) { Log::warning('Local mirror of Supabase deal failed', ['error' => $e->getMessage()]); }
                        return response()->json([
                            'success' => true,
                            'message' => 'Deal created successfully',
                            'deal' => $row
                        ]);
                    } else {
                        Log::warning('Supabase create deal failed, falling back to local DB', ['response' => $resp->body()]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Supabase create deal threw, falling back to local DB', ['error' => $e->getMessage()]);
                }
            }

            // Convert applicable_categories array to JSON if present
            if (isset($dealData['applicable_categories']) && is_array($dealData['applicable_categories'])) {
                $dealData['applicable_categories'] = json_encode($dealData['applicable_categories']);
            }
            // Convert specific_items array to JSON if present
            if (isset($dealData['specific_items']) && is_array($dealData['specific_items'])) {
                $dealData['specific_items'] = json_encode($dealData['specific_items']);
            }
            // Normalize active_days
            if (isset($dealData['active_days']) && is_array($dealData['active_days'])) {
                $dealData['active_days'] = json_encode(array_values(array_unique(array_map('intval', $dealData['active_days']))));
            }
            if (isset($dealData['category_discounts']) && is_array($dealData['category_discounts'])) {
                $dealData['category_discounts'] = json_encode($dealData['category_discounts']);
            }
            if (isset($dealData['item_discounts']) && is_array($dealData['item_discounts'])) {
                $normalized = [];
                foreach ($dealData['item_discounts'] as $k => $v) { $normalized[(string)$k] = $v; }
                $dealData['item_discounts'] = json_encode($normalized);
            }

            $deal = Deal::create($dealData);

            // Send email campaign if requested
            if (!empty($deal->email_customers)) {
                $this->sendDealEmailCampaign($deal);
            }

            Log::info('Deal created successfully', [
                'deal_id' => $deal->id,
                'deal_name' => $deal->name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal created successfully',
                'deal' => $this->formatDealForResponse($deal)
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating deal', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create deal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Support lightweight PATCH for status toggle only
        if ($request->has('is_active') && count($request->all()) === 1) {
            $deal = Deal::findOrFail($id);
            $deal->is_active = (bool)$request->boolean('is_active');
            $deal->save();
            return response()->json([
                'success' => true,
                'message' => 'Deal status updated',
                'deal' => $this->formatDealForResponse($deal)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed,fixed_amount,bogo,bulk',
            'value' => 'required|numeric|min:0',
            'frequency' => 'required|in:always,daily,weekly,monthly,custom',
            'day_of_week' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'applicable_categories' => 'nullable|array',
            'specific_items' => 'nullable|array',
            'category_discounts' => 'nullable|array',
            'item_discounts' => 'nullable|array',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'minimum_purchase_type' => 'nullable|in:dollars,grams',
            'max_uses' => 'nullable|integer|min:1',
            'email_customers' => 'boolean',
            'loyalty_only' => 'boolean',
            'medical_only' => 'boolean',
            'is_active' => 'boolean',
            'active_days' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                try {
                    $payload = $request->all();
                    foreach (['start_date','end_date'] as $dk) {
                        if (isset($payload[$dk]) && (!$payload[$dk] || $payload[$dk] === '')) {
                            $payload[$dk] = null;
                        }
                    }
                    if (isset($payload['applicable_categories']) && is_string($payload['applicable_categories'])) {
                        $payload['applicable_categories'] = json_decode($payload['applicable_categories'], true);
                    }
                    if (isset($payload['specific_items']) && is_string($payload['specific_items'])) {
                        $payload['specific_items'] = json_decode($payload['specific_items'], true);
                    }
                    if (isset($payload['active_days']) && is_string($payload['active_days'])) {
                        $payload['active_days'] = json_decode($payload['active_days'], true);
                    }
                    if (isset($payload['category_discounts']) && is_string($payload['category_discounts'])) {
                        $payload['category_discounts'] = json_decode($payload['category_discounts'], true);
                    }
                    if (isset($payload['item_discounts']) && is_string($payload['item_discounts'])) {
                        $payload['item_discounts'] = json_decode($payload['item_discounts'], true);
                    }
                    $resp = Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Accept' => 'application/json',
                        'Prefer' => 'return=representation'
                    ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/deals?id=eq.' . urlencode($id), $payload);
                    if ($resp->successful()) {
                        $rows = $resp->json();
                        $row = is_array($rows) && isset($rows[0]) ? $rows[0] : $rows;
                        // Mirror to local DB
                        try { $this->upsertLocalDealFromSupabaseRow($row); } catch (\Throwable $e) { Log::warning('Local mirror of Supabase deal failed', ['error' => $e->getMessage()]); }
                        return response()->json([
                            'success' => true,
                            'message' => 'Deal updated successfully',
                            'deal' => $row
                        ]);
                    } else {
                        Log::warning('Supabase update deal failed, falling back to local DB', ['response' => $resp->body()]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Supabase update deal threw, falling back to local DB', ['error' => $e->getMessage()]);
                }
            }

            $deal = Deal::findOrFail($id);
            $dealData = $request->all();

            // Normalize optional dates
            foreach (['start_date','end_date'] as $dk) {
                if (isset($dealData[$dk]) && (!$dealData[$dk] || $dealData[$dk] === '')) {
                    $dealData[$dk] = null;
                }
            }

            // Convert applicable_categories array to JSON if present
            if (isset($dealData['applicable_categories']) && is_array($dealData['applicable_categories'])) {
                $dealData['applicable_categories'] = json_encode($dealData['applicable_categories']);
            }
            // Convert specific_items array to JSON if present
            if (isset($dealData['specific_items']) && is_array($dealData['specific_items'])) {
                $dealData['specific_items'] = json_encode($dealData['specific_items']);
            }
            // Normalize active_days
            if (isset($dealData['active_days']) && is_array($dealData['active_days'])) {
                $dealData['active_days'] = json_encode(array_values(array_unique(array_map('intval', $dealData['active_days']))));
            }
            if (isset($dealData['category_discounts']) && is_array($dealData['category_discounts'])) {
                $dealData['category_discounts'] = json_encode($dealData['category_discounts']);
            }
            if (isset($dealData['item_discounts']) && is_array($dealData['item_discounts'])) {
                $normalized = [];
                foreach ($dealData['item_discounts'] as $k => $v) { $normalized[(string)$k] = $v; }
                $dealData['item_discounts'] = json_encode($normalized);
            }

            $originalEmail = (bool)$deal->email_customers;
            $deal->update($dealData);

            // Send email campaign if newly enabled
            if ($deal->email_customers && !$originalEmail) {
                $this->sendDealEmailCampaign($deal);
            }

            Log::info('Deal updated successfully', [
                'deal_id' => $deal->id,
                'deal_name' => $deal->name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal updated successfully',
                'deal' => $this->formatDealForResponse($deal)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating deal', [
                'deal_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update deal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                try {
                    $resp = Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Accept' => 'application/json'
                    ])->delete(rtrim($supabaseUrl,'/') . '/rest/v1/deals?id=eq.' . urlencode($id));
                    if ($resp->successful()) {
                        Log::info('Deal deleted successfully (Supabase)', [ 'deal_id' => $id, 'user_id' => auth()->id() ]);
                        // Also delete locally if present
                        try { if (\App\Models\Deal::where('id', $id)->exists()) { \App\Models\Deal::where('id', $id)->delete(); } } catch (\Throwable $e) { Log::warning('Local delete mirror failed', ['error' => $e->getMessage()]); }
                        return response()->json([
                            'success' => true,
                            'message' => 'Deal deleted successfully'
                        ]);
                    } else {
                        Log::warning('Supabase delete deal failed, falling back to local DB', ['response' => $resp->body()]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Supabase delete deal threw, falling back to local DB', ['error' => $e->getMessage()]);
                }
            }

            $deal = Deal::findOrFail($id);
            $dealName = $deal->name;

            $deal->delete();

            Log::info('Deal deleted successfully', [
                'deal_id' => $id,
                'deal_name' => $dealName,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting deal', [
                'deal_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function applyDeal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deal_id' => 'required|exists:deals,id',
            'cart_total' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'categories' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                $get = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json'
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/deals', [ 'select' => '*', 'id' => 'eq.' . $request->deal_id ]);
                if (!$get->ok()) throw new \Exception('Supabase fetch error');
                $rows = $get->json();
                $row = is_array($rows) && isset($rows[0]) ? $rows[0] : null;
                if (!$row) return response()->json(['success' => false, 'message' => 'Deal not found'], 404);
                $newUses = (int)($row['current_uses'] ?? 0) + 1;
                $upd = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/deals?id=eq.' . urlencode($request->deal_id), [ 'current_uses' => $newUses ]);
                if (!$upd->successful()) throw new \Exception('Supabase update error');
                $updated = $upd->json();
                $dealRow = is_array($updated) && isset($updated[0]) ? $updated[0] : $row;
                return response()->json([
                    'success' => true,
                    'discount' => 0,
                    'deal' => $dealRow
                ]);
            }

            $deal = Deal::findOrFail($request->deal_id);

            // Check if deal is active by model rules (dates, usage, frequency)
            if (!$deal->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal is not currently active or has expired'
                ], 400);
            }

            // Check loyalty requirement
            if ($deal->loyalty_only && !$request->customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This deal is only available to loyalty program members'
                ], 400);
            }

            // Check minimum purchase requirement
            if ($deal->minimum_purchase && $request->cart_total < $deal->minimum_purchase) {
                $type = $deal->minimum_purchase_type === 'grams' ? 'grams' : 'dollars';
                return response()->json([
                    'success' => false,
                    'message' => "Minimum purchase of {$deal->minimum_purchase} {$type} required"
                ], 400);
            }

            // Check usage limits
            if ($deal->max_uses && $deal->current_uses >= $deal->max_uses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deal usage limit has been reached'
                ], 400);
            }

            // Calculate discount
            $discount = $this->calculateDiscount($deal, $request->cart_total);

            // Update usage count
            $deal->increment('current_uses');

            // Log deal usage
            $this->logDealUsage($deal, $request->customer_id, $discount);

            return response()->json([
                'success' => true,
                'discount' => $discount,
                'deal' => $this->formatDealForResponse($deal)
            ]);

        } catch (\Exception $e) {
            Log::error('Error applying deal', [
                'deal_id' => $request->deal_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to apply deal'
            ], 500);
        }
    }

    public function getAnalytics()
    {
        try {
            $analytics = [
                'total_deals' => Deal::count(),
                'active_deals' => Deal::where('is_active', true)->count(),
                'total_usage' => Deal::sum('current_uses'),
                'loyalty_deals' => Deal::where('loyalty_only', true)->count(),
                'medical_deals' => Deal::where('medical_only', true)->count(),
                'email_campaigns' => Deal::where('email_customers', true)->count(),
                'top_deals' => Deal::orderBy('current_uses', 'desc')->take(5)->get(),
                'recent_deals' => Deal::orderBy('created_at', 'desc')->take(10)->get()
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching deal analytics', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics'
            ], 500);
        }
    }

    private function formatDealForResponse($deal)
    {
        // Support both Eloquent models and array rows (e.g., Supabase)
        if (is_array($deal)) {
            $dealArray = $deal;
        } elseif (is_object($deal) && method_exists($deal, 'toArray')) {
            $dealArray = $deal->toArray();
        } else {
            $dealArray = (array)$deal;
        }

        // Normalize field name variants
        if (!isset($dealArray['value']) && isset($dealArray['discount_value'])) {
            $dealArray['value'] = $dealArray['discount_value'];
        }
        if (!isset($dealArray['applicable_categories']) && isset($dealArray['categories'])) {
            $dealArray['applicable_categories'] = $dealArray['categories'];
        }

        // Normalize/decode JSON fields when stored as strings
        if (array_key_exists('applicable_categories', $dealArray) && is_string($dealArray['applicable_categories'])) {
            $decoded = json_decode($dealArray['applicable_categories'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dealArray['applicable_categories'] = $decoded;
            }
        }
        if (array_key_exists('specific_items', $dealArray) && is_string($dealArray['specific_items'])) {
            $decoded = json_decode($dealArray['specific_items'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dealArray['specific_items'] = $decoded;
            }
        }
        if (array_key_exists('active_days', $dealArray) && is_string($dealArray['active_days'])) {
            $decoded = json_decode($dealArray['active_days'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dealArray['active_days'] = $decoded;
            }
        }
        if (array_key_exists('category_discounts', $dealArray) && is_string($dealArray['category_discounts'])) {
            $decoded = json_decode($dealArray['category_discounts'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dealArray['category_discounts'] = $decoded;
            }
        }
        if (array_key_exists('item_discounts', $dealArray) && is_string($dealArray['item_discounts'])) {
            $decoded = json_decode($dealArray['item_discounts'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dealArray['item_discounts'] = $decoded;
            }
        }

        return $dealArray;
    }

    private function upsertLocalDealFromSupabaseRow($row)
    {
        if (!$row) return;
        $arr = is_array($row) ? $row : (array)$row;
        $mapped = [
            'id' => $arr['id'] ?? null,
            'name' => $arr['name'] ?? null,
            'description' => $arr['description'] ?? null,
            'type' => $arr['type'] ?? 'percentage',
            'value' => $arr['value'] ?? ($arr['discount_value'] ?? 0),
            'frequency' => $arr['frequency'] ?? 'always',
            'day_of_week' => $arr['day_of_week'] ?? null,
            'day_of_month' => $arr['day_of_month'] ?? null,
            'start_date' => $arr['start_date'] ?? null,
            'end_date' => $arr['end_date'] ?? null,
            'applicable_categories' => $arr['applicable_categories'] ?? ($arr['categories'] ?? null),
            'specific_items' => $arr['specific_items'] ?? null,
            'category_discounts' => $arr['category_discounts'] ?? null,
            'item_discounts' => $arr['item_discounts'] ?? null,
            'minimum_purchase' => $arr['minimum_purchase'] ?? null,
            'minimum_purchase_type' => $arr['minimum_purchase_type'] ?? 'dollars',
            'max_uses' => $arr['max_uses'] ?? null,
            'current_uses' => $arr['current_uses'] ?? 0,
            'email_customers' => (bool)($arr['email_customers'] ?? false),
            'loyalty_only' => (bool)($arr['loyalty_only'] ?? false),
            'medical_only' => (bool)($arr['medical_only'] ?? false),
            'is_active' => (bool)($arr['is_active'] ?? true),
            'active_days' => $arr['active_days'] ?? null,
        ];
        // Ensure JSON fields are arrays
        foreach (['applicable_categories','specific_items','active_days','category_discounts','item_discounts'] as $k) {
            if (isset($mapped[$k]) && is_string($mapped[$k])) {
                $dec = json_decode($mapped[$k], true);
                if (json_last_error() === JSON_ERROR_NONE) $mapped[$k] = $dec;
            }
        }
        // Upsert by id if numeric
        $id = $mapped['id'] ?? null;
        if ($id !== null) {
            $existing = \App\Models\Deal::find($id);
            if ($existing) {
                $existing->fill($mapped);
                $existing->save();
            } else {
                // Allow setting ID explicitly
                $deal = new \App\Models\Deal($mapped);
                $deal->id = $id;
                $deal->save();
            }
        } else {
            \App\Models\Deal::updateOrCreate(['name' => $mapped['name']], $mapped);
        }
    }

    private function isDealValid($deal)
    {
        return $deal->isActive();
    }

    private function calculateDiscount($deal, $cartTotal)
    {
        switch ($deal->type) {
            case 'percentage':
                return $cartTotal * ($deal->value / 100);
            case 'fixed_amount':
                return min($deal->value, $cartTotal);
            case 'bogo':
                // Simplified BOGO calculation
                return $cartTotal * ($deal->value / 200); // Half the percentage discount
            case 'bulk':
                return $cartTotal * ($deal->value / 100);
            default:
                return 0;
        }
    }

    private function logDealUsage($deal, $customerId, $discount)
    {
        // Here you would log the deal usage to a usage history table
        Log::info('Deal applied', [
            'deal_id' => $deal->id,
            'customer_id' => $customerId,
            'discount_amount' => $discount,
            'timestamp' => now()
        ]);
    }

    private function sendDealEmailCampaign($deal)
    {
        // Get loyalty program members
        $loyaltyMembers = Customer::whereNotNull('loyalty_member_id')
                                ->whereNotNull('email')
                                ->get();

        foreach ($loyaltyMembers as $customer) {
            try {
                $payloadDeal = $deal->toArray();
                if (!empty($payloadDeal['applicable_categories']) && is_string($payloadDeal['applicable_categories'])) {
                    $decoded = json_decode($payloadDeal['applicable_categories'], true);
                    if (json_last_error() === JSON_ERROR_NONE) $payloadDeal['applicable_categories'] = $decoded;
                }
                Mail::send('emails.deal-notification', [
                    'deal' => $payloadDeal,
                    'customer' => $customer->toArray(),
                ], function ($message) use ($customer, $deal) {
                    $message->to($customer->email)
                        ->subject(($deal->name ? ($deal->name.' - ') : '').'New Deal at '.(config('app.name','Cannabis POS')))
                        ->from(config('mail.from.address', env('MAIL_FROM_ADDRESS')), config('mail.from.name', env('MAIL_FROM_NAME')));
                });

                Log::info('Deal email sent', [
                    'deal_id' => $deal->id,
                    'customer_id' => $customer->id,
                    'email' => $customer->email
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send deal email', [
                    'deal_id' => $deal->id,
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function sendEmailCampaign($id)
    {
        try {
            $deal = Deal::findOrFail($id);
            $this->sendDealEmailCampaign($deal);
            return response()->json([
                'success' => true,
                'message' => 'Email campaign sent successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending deal email campaign', ['deal_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email campaign'
            ], 500);
        }
    }
}

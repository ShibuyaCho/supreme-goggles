<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DemoController;
use App\Http\Controllers\Api\POSController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MetrcController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ProductActionsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Health check and status endpoints
Route::get('/ping', [DemoController::class, 'ping']);
Route::get('/status', [DemoController::class, 'status']);
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Cannabis POS API is healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'laravel_version' => app()->version(),
        'php_version' => PHP_VERSION
    ]);
});

// Public deals API for SPA compatibility
Route::get('/deals', [DealsController::class, 'index']);

// Supabase-backed open reads and writes (no auth) for SPA compatibility
// Customers (read-only open endpoint)
Route::get('/customers-open', function(\Illuminate\Http\Request $request) {
    $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    $search = trim((string)$request->query('search', ''));
    if ($supabaseUrl && $supabaseKey) {
        try {
            $params = [ 'select' => '*' ];
            if ($search !== '') {
                $q = '*' . $search . '*';
                $params['or'] = '(name.ilike.' . $q . ',email.ilike.' . $q . ',phone.ilike.' . $q . ')';
            }
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
            ])->get($supabaseUrl . '/rest/v1/customers', $params);
            if ($resp->ok()) {
                $rows = $resp->json() ?? [];
                return response()->json(['customers' => is_array($rows) ? $rows : []]);
            }
        } catch (\Throwable $e) { /* ignore */ }
    }
    return response()->json(['customers' => []]);
});

// Products (read-only open endpoint)
Route::get('/products-open', function(\Illuminate\Http\Request $request) {
    $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    $search = trim((string)$request->query('search', ''));
    $category = trim((string)$request->query('category', ''));
    if ($supabaseUrl && $supabaseKey) {
        try {
            $params = [ 'select' => '*' ];
            if ($search !== '') {
                $q = '*' . $search . '*';
                $params['or'] = '(name.ilike.' . $q . ',sku.ilike.' . $q . ',metrc_tag.ilike.' . $q . ')';
            }
            if ($category !== '') { $params['category'] = 'eq.' . $category; }
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
            ])->get($supabaseUrl . '/rest/v1/products', $params);
            if ($resp->ok()) {
                $rows = $resp->json() ?? [];
                return response()->json(['products' => is_array($rows) ? $rows : []]);
            }
        } catch (\Throwable $e) { /* ignore */ }
    }
    return response()->json(['products' => []]);
});

// Rooms (open read and create for SPA management)
Route::get('/rooms-open', function(\Illuminate\Http\Request $request) {
    $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    $storeId = (string)($request->header('X-Store-ID') ?: 'default');
    $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
    if ($supabaseUrl && $supabaseKey) {
        try {
            $params = [ 'select' => '*' ];
            if ($storeId) $params['store_id'] = 'eq.' . $storeId;
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,
            ])->get($supabaseUrl . '/rest/v1/rooms', $params);
            if ($resp->ok()) return response()->json(['rooms' => $resp->json() ?? []]);
        } catch (\Throwable $e) { /* ignore */ }
    }
    return response()->json(['rooms' => []]);
});
Route::post('/rooms-open', function(\Illuminate\Http\Request $request) {
    $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if (!$supabaseUrl || !$supabaseKey) return response()->json(['success'=>false,'message'=>'Supabase not configured'],503);
    $storeId = (string)($request->header('X-Store-ID') ?: 'default');
    $storeName = (string)($request->header('X-Store-Name') ?: '');
    $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
    $b = $request->all();
    $row = [
        'store_id' => $storeId,
        'store_name' => $storeName ?: null,
        'name' => $b['name'] ?? ($b['room_name'] ?? 'Room'),
        'type' => $b['type'] ?? ($b['category'] ?? 'storage'),
        'max_capacity' => isset($b['max_capacity']) ? (int)$b['max_capacity'] : null,
        'description' => $b['description'] ?? null,
        'is_active' => array_key_exists('is_active', $b) ? (bool)$b['is_active'] : true,
        'updated_at' => now()->toIso8601String(),
    ];
    try {
        $resp = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
            'X-Store-ID' => $storeId,
        ])->post($supabaseUrl . '/rest/v1/rooms?on_conflict=store_id,name', [ $row ]);
        if ($resp->successful()) {
            $arr = $resp->json(); $created = is_array($arr)&&isset($arr[0])?$arr[0]:$arr; return response()->json(['success'=>true,'room'=>$created],201);
        }
        return response()->json(['success'=>false,'message'=>$resp->body()],400);
    } catch (\Throwable $e) {
        return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
    }
});

// Drawers (open read and create)
Route::get('/drawers-open', function(\Illuminate\Http\Request $request) {
    $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    $storeId = (string)($request->header('X-Store-ID') ?: 'default');
    $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
    $roomId = $request->query('room_id');
    if ($supabaseUrl && $supabaseKey) {
        try {
            $params = [ 'select' => '*' ];
            if ($storeId) $params['store_id'] = 'eq.' . $storeId;
            if ($roomId) $params['room_id'] = 'eq.' . $roomId;
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,
            ])->get($supabaseUrl . '/rest/v1/drawers', $params);
            if ($resp->ok()) return response()->json(['drawers' => $resp->json() ?? []]);
        } catch (\Throwable $e) { /* ignore */ }
    }
    return response()->json(['drawers' => []]);
});
Route::post('/drawers-open', function(\Illuminate\Http\Request $request) {
    $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if (!$supabaseUrl || !$supabaseKey) return response()->json(['success'=>false,'message'=>'Supabase not configured'],503);
    $storeId = (string)($request->header('X-Store-ID') ?: 'default');
    $storeName = (string)($request->header('X-Store-Name') ?: '');
    $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
    $b = $request->all();
    $row = [
        'store_id' => $storeId,
        'store_name' => $storeName ?: null,
        'room_id' => $b['room_id'] ?? null,
        'name' => $b['name'] ?? 'Drawer',
        'status' => $b['status'] ?? 'open',
        'starting_amount' => isset($b['starting_amount']) ? (float)$b['starting_amount'] : 0,
        'current_amount' => isset($b['current_amount']) ? (float)$b['current_amount'] : 0,
        'opened_at' => $b['opened_at'] ?? now()->toIso8601String(),
        'updated_at' => now()->toIso8601String(),
    ];
    try {
        $resp = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'return=representation',
            'X-Store-ID' => $storeId,
        ])->post($supabaseUrl . '/rest/v1/drawers', [ $row ]);
        if ($resp->successful()) {
            $arr = $resp->json(); $created = is_array($arr)&&isset($arr[0])?$arr[0]:$arr; return response()->json(['success'=>true,'drawer'=>$created],201);
        }
        return response()->json(['success'=>false,'message'=>$resp->body()],400);
    } catch (\Throwable $e) {
        return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
    }
});

// Activity logging (best-effort; may be a no-op)
Route::post('/activity', function(\Illuminate\Http\Request $request){
    try { \Illuminate\Support\Facades\Log::info('Activity', ['payload'=>$request->all()]); } catch (\Throwable $e) {}
    return response()->json(['success'=>true]);
});
Route::post('/deals', [DealsController::class, 'store']);
Route::put('/deals/{id}', [DealsController::class, 'update']);
Route::patch('/deals/{id}', [DealsController::class, 'update']);
Route::delete('/deals/{id}', [DealsController::class, 'destroy']);
// Email campaign trigger (rate-limited)
Route::post('/deals/{id}/email', [DealsController::class, 'sendEmailCampaign'])->middleware('throttle:6,1');
// Products list for pickers (returns JSON, supports search/status params)
Route::get('/products', [ProductsController::class, 'index']);

// Public POS settings endpoints for SPA/demo compatibility
Route::get('/settings/pos', function() {
    \Illuminate\Support\Facades\Log::info('Settings GET', ['scope' => 'public', 'store' => (string)request()->header('X-Store-ID')]);
    $defaults = [
        'sales_tax' => 0.0,
        'excise_tax' => 10.0,
        'cannabis_tax' => 17.0,
        'tax_inclusive' => false,
        'store_name' => 'Cannabest POS',
        'store_address' => '',
        'store_phone' => '',
        'store_email' => '',
        'website' => '',
        'store_manager' => '',
        'license_number' => '',
        'receipt_footer' => "Thank you for your business!\nKeep receipt for returns and warranty.",
        'exit_label_categories' => ['Flower','Pre-Rolls','Concentrates','Edibles'],
        'auto_print_receipt' => false,
        'receipt_autoprint' => false,
        'receipt_categories_autoprint' => [],
        'receipt_show_tax_breakdown' => true,
        'receipt_show_metrc' => true,
        'receipt_show_loyalty' => true,
        'receipt_show_qr_code' => false,
        'default_receipt_printer' => '',
        'receipt_paper_size' => '80mm',
        'require_customer' => true,
        'age_verification' => true,
        'limit_enforcement' => true,
        'accept_cash' => true,
        'accept_debit' => true,
        'accept_check' => false,
        'round_to_nearest' => false,
        'minimum_price_enabled' => false,
        'minimum_price_amount' => 0.01,
        'minimum_price_categories' => [],
        'inventory_view_mode' => 'cards',
        'expandable_cart' => true,
        'role_permissions' => [
            'admin' => ['*'],
            'manager' => ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
            'inventory' => ['products:*','metrc:access','metrc:sync','analytics:read'],
            'budtender' => ['pos:*','products:read','customers:read','sales:create','analytics:read'],
            'cashier' => ['pos:*','products:read','sales:create','products:print','analytics:read','pos:scanner_only']
        ],
        'auto_delete_zero_quantity' => false,
        'auto_delete_zero_days' => 1,
        'weight_threshold' => 0,
        'metrc_enabled' => true,
        'metrc_user_key' => env('METRC_USER_KEY', ''),
        'metrc_vendor_key' => env('METRC_VENDOR_KEY', ''),
        'metrc_facility' => env('METRC_FACILITY', ''),
        'metrc_auto_push_sales' => false,
        'dark_mode' => false,
        'theme_color' => 'green',
        'font_size' => 'medium',
        'high_contrast' => false,
        'reduce_motion' => false,
        'business_hours' => [
            ['day' => 'Monday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
            ['day' => 'Tuesday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
            ['day' => 'Wednesday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
            ['day' => 'Thursday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
            ['day' => 'Friday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
            ['day' => 'Saturday', 'is_open' => true, 'open_time' => '10:00', 'close_time' => '20:00'],
            ['day' => 'Sunday', 'is_open' => true, 'open_time' => '11:00', 'close_time' => '19:00'],
        ],
    ];
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    $settingsRemote = [];
    $settingsLocal = [];
    // Multi-store: scope by X-Store-ID header when present
    $storeId = request()->header('X-Store-ID');
    $storeId = is_string($storeId) ? trim($storeId) : '';
    if ($storeId === '' || $storeId === null) $storeId = 'default';
    // sanitize id for safety
    $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
    $updatedAtRemote = null; $updatedAtLocal = null;
    if ($supabaseUrl && $supabaseKey) {
        try {
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
            ]);
            $row = null;
            if ($resp->ok()) {
                $arr = $resp->json();
                $row = (is_array($arr) && isset($arr[0])) ? $arr[0] : null;
            }
            // Legacy fallback: defaultstore
            if (!$row && $storeId === 'default') {
                $resp2 = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                    'id' => 'eq.defaultstore',
                    'select' => 'id,settings,updated_at',
                ]);
                if ($resp2->ok()) {
                    $arr2 = $resp2->json();
                    $row = (is_array($arr2) && isset($arr2[0])) ? $arr2[0] : null;
                }
            }
            if (is_array($row) && isset($row['settings']) && is_array($row['settings'])) {
                $settingsRemote = $row['settings'];
                $updatedAtRemote = $row['updated_at'] ?? null;
            }
        } catch (\Throwable $e) {}
    }
    // Local DB overlay (fills gaps and provides fallback)
    try {
        $local = \Illuminate\Support\Facades\DB::table('pos_settings')->where('id', $storeId)->first();
        if (!$local && $storeId === 'default') {
            $local = \Illuminate\Support\Facades\DB::table('pos_settings')->where('id', 'defaultstore')->first();
        }
        if ($local && isset($local->settings)) {
            $decoded = json_decode($local->settings, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $settingsLocal = $decoded;
                $updatedAtLocal = $local->updated_at ?? null;
            }
        }
    } catch (\Throwable $e) {}

    // Compose final settings: defaults -> remote -> local
    $settings = array_merge($defaults, is_array($settingsRemote)?$settingsRemote:[], is_array($settingsLocal)?$settingsLocal:[]);
    $settingsUpdatedAt = $updatedAtRemote ?? $updatedAtLocal;
    try {
        if ($updatedAtRemote && $updatedAtLocal) {
            $settingsUpdatedAt = strcmp((string)$updatedAtRemote, (string)$updatedAtLocal) >= 0 ? $updatedAtRemote : $updatedAtLocal;
        }
    } catch (\Throwable $e) {}

    // Mask METRC keys in response
    if (array_key_exists('metrc_user_key', $settings)) {
        $settings['metrc_user_key'] = !empty($settings['metrc_user_key']) ? '••••••••' : '';
    }
    if (array_key_exists('metrc_vendor_key', $settings)) {
        $settings['metrc_vendor_key'] = !empty($settings['metrc_vendor_key']) ? '••••••••' : '';
    }
    return response()->json([
        'success' => true,
        'settings' => $settings,
        'settings_updated_at' => $settingsUpdatedAt,
        'tax_rate' => $settings['sales_tax'] ?? 20.0,
        'currency' => 'USD',
        'timezone' => config('app.timezone'),
    ]);
});
Route::post('/settings/pos', function(\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Settings POST', ['scope' => 'public', 'store' => (string)$request->header('X-Store-ID'), 'fields' => array_keys($request->all() ?? [])]);
    $incoming = $request->all();
    // If client sent a nested `settings` object, flatten it into top-level keys
    if (isset($incoming['settings']) && is_array($incoming['settings'])) {
        $nested = $incoming['settings'];
        unset($incoming['settings']);
        $incoming = array_merge($nested, $incoming);
    }
    // Basic validation for critical fields
    $validator = \Illuminate\Support\Facades\Validator::make($incoming, [
        'print_labels' => 'sometimes|boolean',
        'receipt_template' => 'sometimes|in:standard,detailed,minimal',
    ]);
    if ($validator->fails()) {
        return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()], 400);
    }
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    // Multi-store: scope by X-Store-ID header when present
    $storeId = $request->header('X-Store-ID');
    $storeId = is_string($storeId) ? trim($storeId) : '';
    if ($storeId === '' || $storeId === null) $storeId = 'default';
    $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
    try {
        // Merge with current
        $current = [];
        if ($supabaseUrl && $supabaseKey) {
            try {
                $resp0 = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                    'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
                ]);
                if ($resp0->ok()) {
                    $arr = $resp0->json();
                    $row = (is_array($arr) && isset($arr[0])) ? $arr[0] : null;
                    if (is_array($row) && isset($row['settings']) && is_array($row['settings'])) {
                        $current = $row['settings'];
                    }
                }
            } catch (\Throwable $e) {}
        }
        // Preserve existing METRC keys if incoming is masked
        $maskPattern = '/^(?:[•*]+)$/u';
        foreach (['metrc_user_key','metrc_vendor_key'] as $k) {
            if (isset($incoming[$k]) && is_string($incoming[$k]) && preg_match($maskPattern, trim($incoming[$k]))) {
                if (isset($current[$k])) { $incoming[$k] = $current[$k]; }
            }
        }
        $merged = array_merge(is_array($current)?$current:[], is_array($incoming)?$incoming:[]);
        $savedRemote = false;
        if ($supabaseUrl && $supabaseKey) {
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
            'X-Store-ID' => $storeId,

        ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings?on_conflict=id', [[
                'id' => $storeId,
                'store_name' => $merged['store_name'] ?? null,
                'settings' => $merged,
                'updated_at' => now()->toIso8601String(),
            ]]);
            // Also write to legacy id for backward-compatibility
            if ($storeId === 'default' || $storeId === 'defaultstore') {
                try {
                    $legacy = $storeId === 'default' ? 'defaultstore' : 'default';
                    \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
            'X-Store-ID' => $storeId,

        ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings?on_conflict=id', [[
                        'id' => $legacy,
                        'store_name' => $merged['store_name'] ?? null,
                        'settings' => $merged,
                        'updated_at' => now()->toIso8601String(),
                    ]]);
                } catch (\Throwable $e) { /* ignore */ }
            }
            if ($resp->successful()) { $savedRemote = true; }
        }
        // Always persist locally as a fallback for reliability
        try {
            \Illuminate\Support\Facades\DB::table('pos_settings')->updateOrInsert(
                ['id' => $storeId],
                ['settings' => json_encode($merged), 'updated_at' => now()]
            );
        } catch (\Throwable $e) { /* ignore local errors */ }
        if ($savedRemote) {
            $respSettings = $merged;
            if (array_key_exists('metrc_user_key', $respSettings)) {
                $respSettings['metrc_user_key'] = !empty($respSettings['metrc_user_key']) ? '••••••••' : '';
            }
            if (array_key_exists('metrc_vendor_key', $respSettings)) {
                $respSettings['metrc_vendor_key'] = !empty($respSettings['metrc_vendor_key']) ? '••••••••' : '';
            }
            return response()->json(['success' => true, 'settings' => $respSettings]);
        }
        // Remote failed but local saved: still return success with flag
        $respSettings = $merged;
        if (array_key_exists('metrc_user_key', $respSettings)) {
            $respSettings['metrc_user_key'] = !empty($respSettings['metrc_user_key']) ? '••••••••' : '';
        }
        if (array_key_exists('metrc_vendor_key', $respSettings)) {
            $respSettings['metrc_vendor_key'] = !empty($respSettings['metrc_vendor_key']) ? '••••••••' : '';
        }
        return response()->json(['success' => true, 'saved_local' => true, 'settings' => $respSettings]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Public store list for SPA/demo compatibility (no auth)
Route::get('/settings/stores/open', function() {
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    $storeId = 'default';
    $stores = [];
    if ($supabaseUrl && $supabaseKey) {
        try {
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                'select' => 'id,settings,updated_at',
                'order' => 'updated_at.desc'
            ]);
            if ($resp->ok()) {
                $arr = $resp->json();
                foreach ((array)$arr as $row) {
                    $id = (string)($row['id'] ?? '');
                    $name = $id;
                    if (isset($row['settings']) && is_array($row['settings']) && isset($row['settings']['store_name'])) {
                        $name = (string)$row['settings']['store_name'];
                    }
                    $stores[] = [
                        'id' => $id,
                        'name' => $name,
                        'updated_at' => $row['updated_at'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Public store list via Supabase failed', ['error'=>$e->getMessage()]);
        }
    }
    if (empty($stores)) {
        try {
            $rows = \Illuminate\Support\Facades\DB::table('pos_settings')->select('id','settings','updated_at')->orderByDesc('updated_at')->limit(200)->get();
            foreach ($rows as $r) {
                $id = (string)$r->id;
                $name = $id;
                $settings = json_decode($r->settings ?? '{}', true);
                if (json_last_error() === JSON_ERROR_NONE && isset($settings['store_name'])) {
                    $name = (string)$settings['store_name'];
                }
                $stores[] = [ 'id'=>$id, 'name'=>$name, 'updated_at'=>$r->updated_at ];
            }
        } catch (\Throwable $e) { /* ignore */ }
    }
    return response()->json(['success' => true, 'stores' => $stores]);
});

// Loyalty members API (public for SPA compatibility)
Route::get('/loyalty-members', function () {
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if ($supabaseUrl && $supabaseKey) {
        try {
            $params = ['select' => '*'];
            if (request()->has('search')) {
                $q = trim((string)request()->get('search'));
                if ($q !== '') {
                    $params['or'] = sprintf(
                        '(name.ilike.*%1$s*,email.ilike.*%1$s*,phone.ilike.*%1$s*)',
                        $q
                    );
                }
            }
            $resp = null; $ok = false;
            for ($i=0; $i<3; $i++) {
                try {
                    $resp = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Accept' => 'application/json',
                    ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/loyalty_members', $params);
                    if ($resp->ok()) { $ok = true; break; }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Supabase loyalty fetch failed', ['attempt'=>$i+1,'error'=>$e->getMessage()]);
                }
                usleep(100000 * ($i+1));
            }
            if ($ok) {
                return response()->json([
                    'success' => true,
                    'members' => $resp->json() ?? [],
                ]);
            }
        } catch (\Throwable $e) { /* fall through */ }
    }
    return response()->json(['success' => true, 'members' => []]);
});
Route::post('/loyalty-members', function (\Illuminate\Http\Request $request) {
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if (!$supabaseUrl || !$supabaseKey) {
        return response()->json(['success' => false, 'message' => 'Supabase not configured'], 503);
    }
    try {
        $b = $request->all();
        $name = trim((string)($b['name'] ?? ''));
        $fallback = (($b['email'] ?? null) ?: ($b['phone'] ?? null) ?: 'Member');
        $join = substr((string)($b['join_date'] ?? now()->toDateString()), 0, 10);
        $pts = (int)($b['starting_points'] ?? $b['points_balance'] ?? 0);
        $row = [
            'customer_id' => isset($b['customer_id']) ? (int)$b['customer_id'] : null,
            'name' => $name !== '' ? $name : $fallback,
            'email' => $b['email'] ?? null,
            'phone' => $b['phone'] ?? null,
            'join_date' => $join,
            'points_balance' => $pts,
            'points_earned' => $pts,
            'points_redeemed' => 0,
            'tier' => $b['tier'] ?? ($b['loyalty_tier'] ?? 'Bronze'),
            'is_veteran' => (bool)($b['is_veteran'] ?? false),
            'total_spent' => (float)($b['total_spent'] ?? 0),
            'total_visits' => (int)($b['total_visits'] ?? 0),
            'last_visit' => $b['last_visit'] ?? null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
        $resp = null; $success = false;
        for ($i=0; $i<3; $i++) {
            try {
                $resp = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'resolution=merge-duplicates,return=representation',
                ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/loyalty_members', [ $row ]);
                if ($resp->successful()) { $success = true; break; }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Supabase loyalty save failed', ['attempt'=>$i+1,'error'=>$e->getMessage()]);
            }
            usleep(150000 * ($i+1));
        }
        if ($success) {
            $arr = $resp->json();
            $created = is_array($arr) && isset($arr[0]) ? $arr[0] : $arr;
            return response()->json(['success' => true, 'member' => $created], 201);
        }
        return response()->json(['success' => false, 'error' => $resp->body()], 400);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Price tiers API (public for POS compatibility)
Route::get('/price-tiers', function () {
    \Illuminate\Support\Facades\Log::info('Price Tiers GET', ['scope' => 'public']);
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if ($supabaseUrl && $supabaseKey) {
        try {
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/price_tiers', [
                'select' => 'id,name,description,prices,custom_weights,is_active,created_at,updated_at,percentage,rules',
                'order' => 'updated_at.desc',
            ]);
            if ($resp->ok()) {
                return response()->json([
                    'success' => true,
                    'tiers' => $resp->json() ?? [],
                ]);
            }
        } catch (\Throwable $e) { /* fall back below */ }
    }
    // Fallback to local DB if Supabase unavailable
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('price_tiers')) {
            $rows = \App\Models\PriceTier::query()->orderByDesc('updated_at')->get()->map(function($r){
                $discount = 0.0;
                if ($r->type === 'percentage') { $discount = (float)$r->adjustment_value; }
                elseif ($r->type === 'fixed_amount') { $discount = (float)$r->adjustment_value; }
                elseif ($r->type === 'fixed_price') { $discount = 0.0; }
                $cust = 'recreational';
                $ct = $r->customer_types;
                if (is_string($ct) && $ct !== '') { $cust = 'recreational'; }
                elseif (is_array($ct) && !empty($ct)) { $cust = (string)($ct[0] ?? 'recreational'); }
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'description' => $r->description,
                    'status' => $r->is_active ? 'active' : 'inactive',
                    'type' => (string)$r->type,
                    'customer_type' => $cust,
                    'discount' => $discount,
                    'min_quantity' => $r->minimum_quantity,
                    'min_amount' => null,
                    'product_count' => 0,
                    'schedule' => [ 'start_date' => $r->valid_from, 'end_date' => $r->valid_until ],
                    'updated_at' => optional($r->updated_at)->toISOString(),
                ];
            })->values()->all();
            return response()->json(['success' => true, 'tiers' => $rows]);
        }
    } catch (\Throwable $e) { /* ignore and return empty */ }
    return response()->json(['success' => true, 'tiers' => []]);
});

// Explicit open alias to avoid Node route collision
Route::get('/price-tiers-open', function () {
    \Illuminate\Support\Facades\Log::info('Price Tiers OPEN GET', ['scope' => 'public']);
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if ($supabaseUrl && $supabaseKey) {
        try {
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/price_tiers', [
                'select' => 'id,name,description,prices,custom_weights,is_active,created_at,updated_at,percentage,rules',
                'order' => 'updated_at.desc',
            ]);
            if ($resp->ok()) {
                return response()->json([
                    'success' => true,
                    'tiers' => $resp->json() ?? [],
                ]);
            }
        } catch (\Throwable $e) { /* fall back below */ }
    }
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('price_tiers')) {
            $rows = \App\Models\PriceTier::query()->orderByDesc('updated_at')->get()->map(function($r){
                $discount = 0.0;
                if ($r->type === 'percentage') { $discount = (float)$r->adjustment_value; }
                elseif ($r->type === 'fixed_amount') { $discount = (float)$r->adjustment_value; }
                elseif ($r->type === 'fixed_price') { $discount = 0.0; }
                $cust = 'recreational';
                $ct = $r->customer_types;
                if (is_array($ct) && !empty($ct)) { $cust = (string)($ct[0] ?? 'recreational'); }
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'description' => $r->description,
                    'status' => $r->is_active ? 'active' : 'inactive',
                    'type' => (string)$r->type,
                    'customer_type' => $cust,
                    'discount' => $discount,
                    'min_quantity' => $r->minimum_quantity,
                    'min_amount' => null,
                    'product_count' => 0,
                    'schedule' => [ 'start_date' => $r->valid_from, 'end_date' => $r->valid_until ],
                    'updated_at' => optional($r->updated_at)->toISOString(),
                ];
            })->values()->all();
            return response()->json(['success' => true, 'tiers' => $rows]);
        }
    } catch (\Throwable $e) {}
    return response()->json(['success' => true, 'tiers' => []]);
});
Route::post('/price-tiers', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Price Tiers POST', ['scope' => 'public', 'fields' => array_keys($request->all() ?? [])]);
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if (!$supabaseUrl || !$supabaseKey) {
        return response()->json(['success' => false, 'message' => 'Supabase not configured'], 503);
    }
    try {
        $incoming = $request->all();
        $payload = [$incoming];
        $resp = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
        ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/price_tiers', $payload);
        $created = null;
        if ($resp->successful()) {
            $arr = $resp->json();
            $created = is_array($arr) && isset($arr[0]) ? $arr[0] : $arr;
        } else {
            return response()->json(['success' => false, 'message' => $resp->body()], 500);
        }
        // Read-after-write verification (best-effort)
        try {
            if (isset($created['id'])) {
                $verify = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/price_tiers', [
                    'id' => 'eq.' . $created['id'],
                    'select' => 'id,name,description,prices,custom_weights,is_active,created_at,updated_at,percentage,rules',
                ]);
                if ($verify->ok()) {
                    $va = $verify->json();
                    $vr = (is_array($va) && isset($va[0])) ? $va[0] : null;
                    if ($vr) { $created = $vr; }
                }
            }
        } catch (\Throwable $e) { /* ignore */ }
        // Mirror into pos_settings.settings.price_tiers for resilience
        try {
            $storeId = $request->header('X-Store-ID');
            $storeId = is_string($storeId) ? trim($storeId) : '';
            if ($storeId === '' || $storeId === null) $storeId = 'default';
            $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
            $cur = [];
            try {
                $get = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                    'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
                ]);
                if ($get->ok()) {
                    $ga = $get->json();
                    $gr = (is_array($ga) && isset($ga[0])) ? $ga[0] : null;
                    if ($gr && isset($gr['settings']) && is_array($gr['settings'])) $cur = $gr['settings'];
                }
            } catch (\Throwable $e) { /* ignore */ }
            $tiersArr = [];
            if (isset($cur['price_tiers']) && is_array($cur['price_tiers'])) $tiersArr = $cur['price_tiers'];
            elseif (isset($cur['priceTiers']) && is_array($cur['priceTiers'])) $tiersArr = $cur['priceTiers'];
            // Normalize the created/incoming tier into settings format
            $copy = [
                'id' => $created['id'] ?? ($incoming['id'] ?? ($incoming['name'] ?? null)),
                'name' => $created['name'] ?? ($incoming['name'] ?? 'Tier'),
                'description' => $created['description'] ?? ($incoming['description'] ?? ''),
                'prices' => $created['prices'] ?? ($incoming['prices'] ?? []),
                'custom_weights' => $created['custom_weights'] ?? ($incoming['custom_weights'] ?? ($incoming['customWeights'] ?? [])),
                'is_active' => array_key_exists('is_active', $created) ? $created['is_active'] : ($incoming['is_active'] ?? ($incoming['isActive'] ?? true)),
                'created_at' => $created['created_at'] ?? ($incoming['created_at'] ?? now()->toIso8601String()),
                'updated_at' => $created['updated_at'] ?? now()->toIso8601String(),
            ];
            // Upsert into array by id or name
            $didReplace = false;
            foreach ($tiersArr as $i => $t) {
                $tid = $t['id'] ?? null; $tname = isset($t['name']) ? strtolower(trim((string)$t['name'])) : null;
                $cid = $copy['id'] ?? null; $cname = isset($copy['name']) ? strtolower(trim((string)$copy['name'])) : null;
                if (($cid !== null && (string)$tid === (string)$cid) || ($cname && $tname === $cname)) {
                    $tiersArr[$i] = $copy; $didReplace = true; break;
                }
            }
            if (!$didReplace) { $tiersArr[] = $copy; }
            $cur['price_tiers'] = $tiersArr;
            \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
            'X-Store-ID' => $storeId,

        ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings?on_conflict=id', [[
                'id' => $storeId,
                'store_name' => $cur['store_name'] ?? null,
                'settings' => $cur,
                'updated_at' => now()->toIso8601String(),
            ]]);
        } catch (\Throwable $e) { /* ignore */ }
        return response()->json(['success' => true, 'tier' => $created], 201);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});
Route::put('/price-tiers/{id}', function ($id, \Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Price Tiers PUT', ['scope' => 'public', 'id' => $id, 'fields' => array_keys($request->all() ?? [])]);
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_ANON_KEY');
    if (!$supabaseUrl || !$supabaseKey) {
        return response()->json(['success' => false, 'message' => 'Supabase not configured'], 503);
    }
    try {
        $body = $request->all();
        $url = rtrim($supabaseUrl,'/') . '/rest/v1/price_tiers?id=eq.' . urlencode($id);
        $resp = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
        ])->patch($url, $body);
        if ($resp->successful()) {
            $updated = null;
            // Fetch the updated row to ensure consistency
            try {
                $verify = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/price_tiers', [
                    'id' => 'eq.' . $id,
                    'select' => 'id,name,description,prices,custom_weights,is_active,created_at,updated_at,percentage,rules',
                ]);
                if ($verify->ok()) {
                    $va = $verify->json();
                    $vr = (is_array($va) && isset($va[0])) ? $va[0] : null;
                    if ($vr) { $updated = $vr; }
                }
            } catch (\Throwable $e) { /* ignore */ }
            if ($updated === null) {
                $arr = $resp->json();
                $updated = is_array($arr) && isset($arr[0]) ? $arr[0] : $arr;
            }
            // Mirror into pos_settings.settings.price_tiers for resilience
            try {
                $storeId = $request->header('X-Store-ID');
                $storeId = is_string($storeId) ? trim($storeId) : '';
                if ($storeId === '' || $storeId === null) $storeId = 'default';
                $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
                $cur = [];
                try {
                    $get = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                        'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
                    ]);
                    if ($get->ok()) {
                        $ga = $get->json();
                        $gr = (is_array($ga) && isset($ga[0])) ? $ga[0] : null;
                        if ($gr && isset($gr['settings']) && is_array($gr['settings'])) $cur = $gr['settings'];
                    }
                } catch (\Throwable $e) { /* ignore */ }
                $tiersArr = [];
                if (isset($cur['price_tiers']) && is_array($cur['price_tiers'])) $tiersArr = $cur['price_tiers'];
                elseif (isset($cur['priceTiers']) && is_array($cur['priceTiers'])) $tiersArr = $cur['priceTiers'];
                $copy = [
                    'id' => $updated['id'] ?? $id,
                    'name' => $updated['name'] ?? ($body['name'] ?? 'Tier'),
                    'description' => $updated['description'] ?? ($body['description'] ?? ''),
                    'prices' => $updated['prices'] ?? ($body['prices'] ?? []),
                    'custom_weights' => $updated['custom_weights'] ?? ($body['custom_weights'] ?? ($body['customWeights'] ?? [])),
                    'is_active' => array_key_exists('is_active', $updated) ? $updated['is_active'] : ($body['is_active'] ?? ($body['isActive'] ?? true)),
                    'created_at' => $updated['created_at'] ?? ($body['created_at'] ?? now()->toIso8601String()),
                    'updated_at' => $updated['updated_at'] ?? now()->toIso8601String(),
                ];
                $didReplace = false;
                foreach ($tiersArr as $i => $t) {
                    $tid = $t['id'] ?? null; $tname = isset($t['name']) ? strtolower(trim((string)$t['name'])) : null;
                    $cid = $copy['id'] ?? null; $cname = isset($copy['name']) ? strtolower(trim((string)$copy['name'])) : null;
                    if (($cid !== null && (string)$tid === (string)$cid) || ($cname && $tname === $cname)) {
                        $tiersArr[$i] = $copy; $didReplace = true; break;
                    }
                }
                if (!$didReplace) { $tiersArr[] = $copy; }
                $cur['price_tiers'] = $tiersArr;
                \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
            'X-Store-ID' => $storeId,

        ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings?on_conflict=id', [[
                'id' => $storeId,
                'store_name' => $cur['store_name'] ?? null,
                'settings' => $cur,
                'updated_at' => now()->toIso8601String(),
            ]]);
            } catch (\Throwable $e) { /* ignore */ }
            return response()->json(['success' => true, 'tier' => $updated]);
        }
        return response()->json(['success' => false, 'message' => $resp->body()], 500);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/pin-login', [AuthController::class, 'pinLogin']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/self-register', [AuthController::class, 'selfRegister']);
});

// TEMP: Open POS payment endpoint for end-to-end testing (no auth, no CSRF under API middleware)
Route::post('/pos/process-payment-open', [\App\Http\Controllers\POSController::class, 'processPayment']);

// Public analytics read-only aliases (no auth required)
Route::prefix('analytics')->group(function () {
    Route::get('/overview-open', [\App\Http\Controllers\AnalyticsController::class, 'overview']);
    Route::get('/end-of-day-open', [\App\Http\Controllers\AnalyticsController::class, 'endOfDay']);
    Route::get('/company-open', [\App\Http\Controllers\AnalyticsController::class, 'companyView']);
    // Public ASPD (pace) endpoint - always Month-To-Date
    Route::get('/aspd-open', [\App\Http\Controllers\AnalyticsController::class, 'getASPDAnalyticsOpen']);
});

// Compatibility aliases (support clients using /api/* without /auth prefix)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/pin-login', [AuthController::class, 'pinLogin']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/self-register', [AuthController::class, 'selfRegister']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentication management
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/verify-pin', [AuthController::class, 'verifyPin']);
        Route::post('/verify-metrc', [AuthController::class, 'verifyMetrc'])
            ->middleware('permission:metrc:access');
        Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('api.verification.send');
    });

    // Compatibility aliases for clients calling /api/logout and /api/refresh
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // User management
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    /*
    |--------------------------------------------------------------------------
    | METRC Integration Routes
    |--------------------------------------------------------------------------
    */
    // Admin debug route (no permission gate, still requires auth + role)
    Route::get('/metrc/debug/packages', [MetrcController::class, 'debugPackages'])
        ->middleware(['role:admin','throttle:10,1']);

    Route::prefix('metrc')->middleware(['permission:metrc:access','throttle:60,1'])->group(function () {
        Route::get('/status', function() {
            $svc = app(\App\Services\MetrcService::class);
            $configured = $svc->isConfigured();
            $test = null; $ok = false;
            try { $test = $svc->testConnection(); $ok = (bool)($test['success'] ?? false); } catch (\Throwable $e) { $ok = false; }
            return response()->json([
                'connected' => $configured && $ok,
                'configured' => $configured,
                'test' => $test,
                'facility' => env('METRC_FACILITY') ?: (\Illuminate\Support\Facades\Cache::get('pos_settings')['metrc_facility'] ?? null),
                'timestamp' => now()->toIso8601String(),
            ]);
        });
        Route::get('/test-connection', [MetrcController::class, 'testConnection']);
        Route::get('/packages', [MetrcController::class, 'getAllPackages']);
        Route::post('/import-packages', [MetrcController::class, 'importActivePackages'])
            ->middleware('permission:products:write');
        Route::post('/sync-inventory', [MetrcController::class, 'syncInventory']);
        Route::get('/packages/{packageTag}', [MetrcController::class, 'getPackageDetails']);
        Route::get('/packages/{packageTag}/history', [MetrcController::class, 'getPackageHistory']);
        Route::get('/transfers/incoming', [MetrcController::class, 'getIncomingTransfers']);
        Route::post('/packages/update-status', [MetrcController::class, 'updatePackageStatus']);
        Route::post('/packages/change-location', [MetrcController::class, 'changePackageLocation']);
    });

    // POS hold/end sale for SPA (token-auth via Sanctum)
    Route::prefix('pos')->group(function () {
        Route::post('/save-sale', function(\Illuminate\Http\Request $request) {
            $user = auth()->user();
            $employee = $user?->employee;
            $name = $request->input('name') ?: ('Held Sale - ' . now()->toDateTimeString());
            $saved = \App\Models\SavedSale::create([
                'name' => $name,
                'employee_id' => $employee->id ?? $user?->id,
                'employee_name' => $employee->full_name ?? ($user?->name ?? 'Employee'),
                'customer_type' => $request->input('customer_type','rec'),
                'customer_info' => $request->input('customer') ?: $request->input('customer_info', []),
                'cart_items' => $request->input('cart_items', []),
                'cart_discount' => $request->input('cart_discount'),
                'selected_loyalty_customer' => $request->input('selected_loyalty_customer'),
                'total_items' => (int)($request->input('total_items') ?? collect($request->input('cart_items', []))->sum('quantity')),
                'total_amount' => (float)($request->input('total_amount') ?? 0),
                'notes' => $request->input('notes','Held from SPA'),
                'status' => 'active',
            ]);
            return response()->json(['success' => true, 'saved_sale_id' => $saved->id]);
        })->middleware('permission:pos:*');

        Route::post('/end-sale', function() {
            // Stateless endpoint for SPA; nothing to clear server-side
            return response()->json(['success' => true, 'message' => 'Sale ended']);
        })->middleware('permission:pos:*');

        // Saved sales management for SPA
        Route::get('/saved-sales', function() {
            $user = auth()->user();
            $employeeId = optional($user?->employee)->id ?? $user?->id;
            $list = \App\Models\SavedSale::active()->byEmployee($employeeId)->orderByDesc('created_at')->get();
            return response()->json(['success' => true, 'saved_sales' => $list]);
        })->middleware('permission:pos:*');
        Route::get('/saved-sales/{id}', function($id){
            $user = auth()->user();
            $employeeId = optional($user?->employee)->id ?? $user?->id;
            $sale = \App\Models\SavedSale::where('employee_id', $employeeId)->findOrFail($id);
            return response()->json(['success' => true, 'saved_sale' => $sale]);
        })->middleware('permission:pos:*');
        Route::delete('/saved-sales/{id}', function($id){
            $user = auth()->user();
            $employeeId = optional($user?->employee)->id ?? $user?->id;
            $sale = \App\Models\SavedSale::where('employee_id', $employeeId)->findOrFail($id);
            $sale->delete();
            return response()->json(['success' => true]);
        })->middleware('permission:pos:*');
        Route::post('/packages/create', [MetrcController::class, 'createPackage'])
            ->middleware('permission:metrc:create');
        Route::post('/products/{product}/sync', [MetrcController::class, 'syncProduct'])
            ->middleware('permission:metrc:sync');
        Route::post('/sales/receipts', [MetrcController::class, 'createSalesReceipt'])
            ->middleware('permission:metrc:sales');
        Route::post('/sales/receipts/from-sale/{sale}', [MetrcController::class, 'createReceiptFromSale'])
            ->middleware('permission:metrc:sales');
        Route::get('/sales/receipts', [MetrcController::class, 'getSalesReceipts']);
        // Sales Deliveries v2
        Route::post('/sales/deliveries', [MetrcController::class, 'createSalesDeliveries'])
            ->middleware('permission:metrc:sales');
        Route::post('/sales/deliveries/from-sale/{sale}', [MetrcController::class, 'createDeliveriesFromSale'])
            ->middleware('permission:metrc:sales');
        Route::get('/facility', [MetrcController::class, 'getFacilityDetails']);
        Route::get('/categories', [MetrcController::class, 'getItemCategories']);
        Route::get('/tags/package/available', [MetrcController::class, 'getAvailablePackageTags']);
        Route::get('/tags/plant/available', [MetrcController::class, 'getAvailablePlantTags']);
        Route::get('/strains/{id}', [MetrcController::class, 'getStrain']);
        Route::get('/items/{id}', [MetrcController::class, 'getItem']);
        Route::get('/items/active', [MetrcController::class, 'getActiveItems']);
        Route::post('/retailid/packages/info', [MetrcController::class, 'getRetailIdPackagesInfo']);
    });

    /*
    |--------------------------------------------------------------------------
    | POS Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('pos')->middleware('permission:pos:access')->group(function () {
        Route::get('/customers/search', [POSController::class, 'searchCustomers']);
        Route::post('/process-payment', [POSController::class, 'processPayment'])
            ->middleware('permission:pos:sales');
        Route::post('/log-age-verification', [POSController::class, 'logAgeVerification']);
        Route::get('/config', [POSController::class, 'getConfig']);
        Route::get('/queue-orders', [POSController::class, 'getQueueOrders']);
    });

    /*
    |--------------------------------------------------------------------------
    | Product Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->group(function () {
        // Read operations (most roles can read products)
        Route::middleware('permission:products:read')->group(function () {
            Route::get('/', [ProductsController::class, 'index']);
            Route::get('/search', [ProductsController::class, 'search']);
            Route::get('/categories', [ProductsController::class, 'getCategories']);
            Route::get('/rooms', [ProductsController::class, 'getRooms']);
            Route::get('/{product}', [ProductsController::class, 'show']);
            Route::get('/{product}/metrc-details', [ProductActionsController::class, 'getMetrcDetails']);
            Route::get('/available-rooms', [ProductActionsController::class, 'getAvailableRooms']);
        });

        // Write operations (inventory management permission required)
        Route::middleware('permission:products:write')->group(function () {
            Route::post('/', [ProductsController::class, 'store']);
            Route::put('/{product}', [ProductsController::class, 'update']);
            Route::delete('/{product}', [ProductsController::class, 'destroy']);
            Route::post('/bulk-update', [ProductsController::class, 'bulkUpdate']);
            Route::put('/{product}/update', [ProductActionsController::class, 'updateProduct']);
        });

        // Special operations
        Route::post('/transfer-room', [ProductActionsController::class, 'transferRoom'])
            ->middleware('permission:products:transfer');
        Route::post('/{product}/print-barcode', [ProductActionsController::class, 'printBarcode'])
            ->middleware('permission:products:print');
        Route::post('/{product}/print-exit-label', [ProductActionsController::class, 'printExitLabel'])
            ->middleware('permission:products:print');
        Route::delete('/{product}/delete', [ProductActionsController::class, 'deleteProduct'])
            ->middleware('permission:products:delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('customers')->group(function () {
        // Read operations
        Route::middleware('permission:customers:read')->group(function () {
            Route::get('/', [CustomersController::class, 'index']);
            Route::get('/search', [CustomersController::class, 'search']);
            Route::get('/{customer}', [CustomersController::class, 'show']);
            Route::get('/{customer}/loyalty', [CustomersController::class, 'getLoyaltyInfo']);
        });

        // Write operations
        Route::middleware('permission:customers:write')->group(function () {
            Route::post('/', [CustomersController::class, 'store']);
            Route::put('/{customer}', [CustomersController::class, 'update']);
            Route::delete('/{customer}', [CustomersController::class, 'destroy']);
            Route::post('/{customer}/add-points', [CustomersController::class, 'addLoyaltyPoints']);
        });

        // Analytics (manager+ only)
        Route::get('/analytics', [CustomersController::class, 'getAnalytics'])
            ->middleware('role:manager,admin');
    });

    /*
    |--------------------------------------------------------------------------
    | Sales Management Routes
    |--------------------------------------------------------------------------
    */
    // Analytics API
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/end-of-day', [AnalyticsController::class, 'endOfDay']);
        Route::get('/company', [AnalyticsController::class, 'companyView']);
        Route::get('/aspd', [AnalyticsController::class, 'getASPDAnalytics']);
    });

    Route::prefix('sales')->group(function () {
        // Read operations
        Route::middleware('permission:sales:read')->group(function () {
            Route::get('/', [SalesController::class, 'index']);
            Route::get('/{sale}', [SalesController::class, 'show']);
            Route::get('/{sale}/receipt', [SalesController::class, 'getReceipt']);
        });

        // Create sales (POS operations)
        Route::post('/', [SalesController::class, 'store'])
            ->middleware('permission:sales:create');

        // Management operations
        Route::middleware('permission:sales:manage')->group(function () {
            Route::put('/{sale}', [SalesController::class, 'update']);
            Route::post('/{sale}/void', [SalesController::class, 'voidSale']);
            Route::post('/{sale}/refund', [SalesController::class, 'refundSale']);
        });

        // Reports and analytics
        Route::middleware('role:manager,admin')->group(function () {
            Route::get('/analytics', [SalesController::class, 'getAnalytics']);
            Route::get('/daily-report', [SalesController::class, 'getDailyReport']);
            Route::get('/tax-report', [SalesController::class, 'getTaxReport']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Employee Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('employees')->group(function () {
        // Basic read access for most users
        Route::get('/', [EmployeesController::class, 'index'])
            ->middleware('permission:employees:read');
        
        // Management operations (admin/manager only)
        Route::middleware('role:admin,manager')->group(function () {
            Route::get('/next-id', [EmployeesController::class, 'nextId']);
            Route::post('/', [EmployeesController::class, 'store']);
            Route::get('/{employee}', [EmployeesController::class, 'show']);
            Route::put('/{employee}', [EmployeesController::class, 'update']);
            Route::delete('/{employee}', [EmployeesController::class, 'destroy']);
            Route::get('/{employee}/performance', [EmployeesController::class, 'getPerformance']);
            Route::get('/schedule', [EmployeesController::class, 'getSchedule']);
            // Resets
            Route::post('/{employee}/reset-pin', [EmployeesController::class, 'resetPin']);
            Route::post('/{employee}/reset-password', [EmployeesController::class, 'sendPasswordReset']);

            // Time clock admin endpoints
            Route::get('/time-entries', [EmployeesController::class, 'listTimeEntries']);
            Route::post('/time-entries', [EmployeesController::class, 'createTimeEntry']);
            Route::put('/time-entries/{entry}', [EmployeesController::class, 'updateTimeEntry']);
        });

        // Clock in/out (all employees)
        Route::get('/{employee}/clock-status', [EmployeesController::class, 'clockStatus']);
        Route::post('/{employee}/clock-in', [EmployeesController::class, 'clockIn']);
        Route::post('/{employee}/clock-out', [EmployeesController::class, 'clockOut']);
    });

    /*
    |--------------------------------------------------------------------------
    | Analytics Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('analytics')->middleware('permission:analytics:read')->group(function () {
        // Real-time JSON endpoints
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/company', [AnalyticsController::class, 'companyView']);
        Route::get('/end-of-day', [AnalyticsController::class, 'endOfDay']);
        // Existing endpoints (keep for compatibility)
        Route::get('/aspd', [AnalyticsController::class, 'getASPDAnalytics']);
        Route::get('/aspd-open', [AnalyticsController::class, 'getASPDAnalyticsOpen']);

    // Supabase-backed open reads (primary source)
    Route::get('/customers-open', function(\Illuminate\Http\Request $request) {
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        $search = trim((string)$request->query('search', ''));
        if ($supabaseUrl && $supabaseKey) {
            try {
                $params = [ 'select' => '*' ];
                if ($search !== '') {
                    $q = '*' . $search . '*';
                    $params['or'] = '(name.ilike.' . $q . ',email.ilike.' . $q . ',phone.ilike.' . $q . ')';
                }
                $resp = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get($supabaseUrl . '/rest/v1/customers', $params);
                if ($resp->ok()) {
                    $rows = $resp->json() ?? [];
                    return response()->json(['customers' => is_array($rows) ? $rows : []]);
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        return response()->json(['customers' => []]);
    });

    Route::get('/products-open', function(\Illuminate\Http\Request $request) {
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        $search = trim((string)$request->query('search', ''));
        $category = trim((string)$request->query('category', ''));
        if ($supabaseUrl && $supabaseKey) {
            try {
                $params = [ 'select' => '*' ];
                if ($search !== '') {
                    $q = '*' . $search . '*';
                    $params['or'] = '(name.ilike.' . $q . ',sku.ilike.' . $q . ',metrc_tag.ilike.' . $q . ')';
                }
                if ($category !== '') { $params['category'] = 'eq.' . $category; }
                $resp = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get($supabaseUrl . '/rest/v1/products', $params);
                if ($resp->ok()) {
                    $rows = $resp->json() ?? [];
                    return response()->json(['products' => is_array($rows) ? $rows : []]);
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        return response()->json(['products' => []]);
    });
    });

    /*
    |--------------------------------------------------------------------------
    | Deals and Promotions Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('deals')->group(function () {
        // Read operations
        Route::middleware('permission:deals:read')->group(function () {
            Route::get('/', [DealsController::class, 'index']);
            Route::get('/{deal}', [DealsController::class, 'show']);
        });

        // Apply deals (POS operations)
        Route::post('/apply', [DealsController::class, 'applyDeal'])
            ->middleware('permission:deals:apply');

        // Management operations
        Route::middleware('permission:deals:manage')->group(function () {
            Route::post('/', [DealsController::class, 'store']);
            Route::put('/{deal}', [DealsController::class, 'update']);
            Route::delete('/{deal}', [DealsController::class, 'destroy']);
            Route::post('/{deal}/email', [DealsController::class, 'sendEmailCampaign']);
        });

        // Analytics
        Route::get('/analytics', [DealsController::class, 'getAnalytics'])
            ->middleware('role:manager,admin');
    });

    /*
    |--------------------------------------------------------------------------
    | Loyalty Program Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('loyalty')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])
            ->middleware('permission:loyalty:read');
        Route::post('/enroll', [LoyaltyController::class, 'enroll'])
            ->middleware('permission:loyalty:enroll');
        Route::post('/{customer}/adjust-points', [LoyaltyController::class, 'adjustPoints'])
            ->middleware('permission:loyalty:manage');
        Route::post('/{customer}/earn-points', [LoyaltyController::class, 'earnPoints'])
            ->middleware('permission:loyalty:manage');
        Route::post('/{customer}/redeem-points', [LoyaltyController::class, 'redeemPoints'])
            ->middleware('permission:loyalty:manage');
        Route::delete('/{customer}', [LoyaltyController::class, 'destroy'])
            ->middleware('role:admin,manager');
        Route::get('/analytics', [LoyaltyController::class, 'getAnalytics'])
            ->middleware('role:manager,admin');
    });

    /*
    |--------------------------------------------------------------------------
    | Inventory Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventory')->middleware('permission:inventory:read')->group(function () {
        Route::get('/report', [ProductsController::class, 'getInventoryReport']);
        Route::get('/low-stock', [ProductsController::class, 'getLowStockItems']);
        Route::get('/room-transfers', [ProductsController::class, 'getRoomTransfers']);
        Route::post('/bulk-transfer', [ProductsController::class, 'bulkRoomTransfer'])
            ->middleware('permission:inventory:transfer');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->middleware('permission:reports:read')->group(function () {
        Route::get('/sales', [SalesController::class, 'getSalesReport']);
        Route::get('/inventory', [ProductsController::class, 'getInventoryReport']);
        Route::get('/customers', [CustomersController::class, 'getCustomerReport']);
        Route::get('/compliance', [SalesController::class, 'getComplianceReport']);
        Route::get('/tax', [SalesController::class, 'getTaxReport']);
        Route::get('/metrc', [ProductsController::class, 'getMetrcReport'])
            ->middleware('permission:metrc:access');

        // Enhanced export functionality
        Route::post('/export', [App\Http\Controllers\EnhancedReportsController::class, 'exportReport'])
            ->middleware('permission:reports:export');
        Route::get('/available', [App\Http\Controllers\EnhancedReportsController::class, 'getAvailableReports']);

        // Report templates (saved reports)
        Route::get('/templates', [App\Http\Controllers\ReportTemplatesController::class, 'index'])
            ->middleware('permission:reports:read');
        Route::post('/templates', [App\Http\Controllers\ReportTemplatesController::class, 'store'])
            ->middleware('permission:reports:read');
        Route::get('/templates/{template}', [App\Http\Controllers\ReportTemplatesController::class, 'show'])
            ->middleware('permission:reports:read');
        Route::put('/templates/{template}', [App\Http\Controllers\ReportTemplatesController::class, 'update'])
            ->middleware('permission:reports:read');
        Route::delete('/templates/{template}', [App\Http\Controllers\ReportTemplatesController::class, 'destroy'])
            ->middleware('permission:reports:read');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings and Configuration Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->group(function () {
        // Read settings (most users)
        Route::get('/pos', function() {
            \Illuminate\Support\Facades\Log::info('Settings GET', ['scope' => 'protected', 'store' => (string)request()->header('X-Store-ID')]);
            // Try Supabase REST first if configured
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            $cached = null;
            $storeId = request()->header('X-Store-ID');
            if (!$storeId) { $storeId = request()->query('store'); }
            $storeId = is_string($storeId) ? trim($storeId) : '';
            if ($storeId === '' || $storeId === null) $storeId = 'default';
            $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
            if ($supabaseUrl && $supabaseKey) {
                try {
                    $resp = null; $ok = false;
                    for ($i=0; $i<3; $i++) {
                        try {
                            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                                'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
                            ]);
                            if ($resp->ok()) { $ok = true; break; }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Supabase settings fetch failed', ['attempt'=>$i+1,'error'=>$e->getMessage()]);
                        }
                        usleep(100000 * ($i+1));
                    }
                    if ($ok) {
                        $arr = $resp->json();
                        $row = (is_array($arr) && isset($arr[0])) ? $arr[0] : null;
                        if (is_array($row) && isset($row['settings']) && is_array($row['settings'])) {
                            $cached = $row['settings'];
                            try { \Illuminate\Support\Facades\Cache::put('pos_settings:' . $storeId, $cached, now()->addYears(5)); } catch (\Throwable $e) {}
                        }
                    }
                } catch (\Throwable $e) { /* fall back */ }
            }
            if (!$cached) {
                try {
                    $row = \Illuminate\Support\Facades\DB::table('pos_settings')->where('id', $storeId)->first();
                    if ($row && isset($row->settings)) {
                        $decoded = json_decode($row->settings, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $cached = $decoded;
                            try { \Illuminate\Support\Facades\Cache::put('pos_settings:' . $storeId, $cached, now()->addYears(5)); } catch (\Throwable $e) {}
                        }
                    }
                } catch (\Throwable $e) {}
            }
            $defaults = [
                // Taxes
                'sales_tax' => 0.0,
                'excise_tax' => 10.0,
                'cannabis_tax' => 17.0,
                'tax_inclusive' => false,

                // Store info
                'store_name' => 'Cannabest POS',
                'store_address' => '',
                'store_phone' => '',
                'store_email' => '',
                'website' => '',
                'store_manager' => '',
                'license_number' => '',
                'receipt_footer' => "Thank you for your business!\nKeep receipt for returns and warranty.",

                // Exit labels
                'exit_label_categories' => ['Flower','Pre-Rolls','Concentrates','Edibles'],

                // Receipt & printing
                'auto_print_receipt' => false,
                'receipt_autoprint' => false,
                'receipt_categories_autoprint' => [],
                'receipt_show_tax_breakdown' => true,
                'receipt_show_metrc' => true,
                'receipt_show_loyalty' => true,
                'receipt_show_qr_code' => false,
                'default_receipt_printer' => '',
                'receipt_paper_size' => '80mm',

                // POS behavior / payments
                'require_customer' => true,
                'age_verification' => true,
                'limit_enforcement' => true,
                'accept_cash' => true,
                'accept_debit' => true,
                'accept_check' => false,
                'round_to_nearest' => false,

                // Pricing
                'minimum_price_enabled' => false,
                'minimum_price_amount' => 0.01,
                'minimum_price_categories' => [],

                // Display & inventory
                'inventory_view_mode' => 'cards',
                'expandable_cart' => true,

                // Role-based permissions (defaults)
                'role_permissions' => [
                    'admin' => ['*'],
                    'manager' => ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
                    'inventory' => ['products:*','metrc:access','metrc:sync','analytics:read'],
                    'budtender' => ['pos:*','products:read','customers:read','sales:create','analytics:read'],
                    'cashier' => ['pos:*','products:read','sales:create','products:print','analytics:read','pos:scanner_only']
                ],

                // Auto delete
                'auto_delete_zero_quantity' => false,
                'auto_delete_zero_days' => 1,

                // METRC
                'metrc_enabled' => config('services.metrc.enabled', true),

                // Appearance
                'dark_mode' => false,
                'theme_color' => 'green',
                'font_size' => 'medium',
                'high_contrast' => false,
                'reduce_motion' => false,

                // Business hours
                'business_hours' => [
                    ['day' => 'Monday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                    ['day' => 'Tuesday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                    ['day' => 'Wednesday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                    ['day' => 'Thursday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                    ['day' => 'Friday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                    ['day' => 'Saturday', 'is_open' => true, 'open_time' => '10:00', 'close_time' => '20:00'],
                    ['day' => 'Sunday', 'is_open' => true, 'open_time' => '11:00', 'close_time' => '19:00'],
                ],
            ];
            $settings = array_merge($defaults, is_array($cached) ? $cached : []);
            // Ensure METRC credentials are available to the settings UI (do not overwrite cached values)
            // Prefer per-user METRC user key from linked employee when available
            if (!array_key_exists('metrc_user_key', $settings) || empty($settings['metrc_user_key'])) {
                $empKey = null;
                try {
                    $user = auth()->user();
                    if ($user && $user->employee && !empty($user->employee->metrc_api_key)) {
                        $empKey = $user->employee->metrc_api_key;
                    }
                } catch (\Throwable $e) {
                    $empKey = null;
                }
                $settings['metrc_user_key'] = $empKey ?? env('METRC_USER_KEY', '');
            }
            if (!array_key_exists('metrc_vendor_key', $settings) || empty($settings['metrc_vendor_key'])) {
                $settings['metrc_vendor_key'] = env('METRC_VENDOR_KEY', '');
            }
            if (!array_key_exists('metrc_facility', $settings) || empty($settings['metrc_facility'])) {
                $settings['metrc_facility'] = env('METRC_FACILITY', '');
            }
            // Mask METRC keys in response
            if (array_key_exists('metrc_user_key', $settings)) {
                $settings['metrc_user_key'] = !empty($settings['metrc_user_key']) ? '••••••••' : '';
            }
            if (array_key_exists('metrc_vendor_key', $settings)) {
                $settings['metrc_vendor_key'] = !empty($settings['metrc_vendor_key']) ? '•••••��••' : '';
            }
            return response()->json([
                'success' => true,
                'settings' => $settings,
                'tax_rate' => $settings['sales_tax'] ?? 20.0,
                'currency' => 'USD',
                'timezone' => config('app.timezone'),
                'features' => [
                    'metrc_integration' => (bool)($settings['metrc_enabled'] ?? true),
                    'loyalty_program' => true,
                    'age_verification' => (bool)($settings['age_verification'] ?? true)
                ]
            ]);
        });

        // Save POS settings (persist to DB and cache)
        Route::post('/pos', function(\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Settings POST', ['scope' => 'protected', 'store' => (string)$request->header('X-Store-ID'), 'fields' => array_keys($request->all() ?? [])]);
            try {
                $settings = $request->all();
                // Basic validation for critical fields
                $validator = \Illuminate\Support\Facades\Validator::make($settings, [
                    'print_labels' => 'sometimes|boolean',
                    'receipt_template' => 'sometimes|in:standard,detailed,minimal',
                ]);
                if ($validator->fails()) {
                    return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()], 400);
                }
                // Initialize Supabase and store scope before any reads
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_ANON_KEY');
                $storeId = $request->header('X-Store-ID');
                if (!$storeId) { $storeId = $request->query('store'); }
                $storeId = is_string($storeId) ? trim($storeId) : '';
                if ($storeId === '' || $storeId === null) $storeId = 'default';
                $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
                // Merge with current to avoid overwriting other fields
                try {
                    $current = [];
                    if ($supabaseUrl && $supabaseKey) {
                        $resp0 = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                            'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
                        ]);
                        if ($resp0->ok()) {
                            $arr0 = $resp0->json();
                            $row0 = (is_array($arr0) && isset($arr0[0])) ? $arr0[0] : null;
                            if ($row0 && isset($row0['settings']) && is_array($row0['settings'])) $current = $row0['settings'];
                        }
                    } else {
                        $row = \Illuminate\Support\Facades\DB::table('pos_settings')->where('id', $storeId)->first();
                        if ($row && isset($row->settings)) {
                            $decoded = json_decode($row->settings, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $current = $decoded;
                        }
                    }
                    if (is_array($current)) {
                        // Preserve existing METRC keys if incoming is masked
                        $maskPattern = '/^(?:[•*]+)$/u';
                        foreach (['metrc_user_key','metrc_vendor_key'] as $k) {
                            if (isset($settings[$k]) && is_string($settings[$k]) && preg_match($maskPattern, trim($settings[$k]))) {
                                if (isset($current[$k])) { $settings[$k] = $current[$k]; }
                            }
                        }
                        $settings = array_merge($current, $settings);
                    }
                } catch (\Throwable $e) { /* ignore */ }
                foreach (['exit_label_categories','receipt_categories_autoprint','minimum_price_categories','role_permissions'] as $field) {
                    if (isset($settings[$field]) && is_string($settings[$field])) {
                        $decoded = json_decode($settings[$field], true);
                        if (json_last_error() === JSON_ERROR_NONE) $settings[$field] = $decoded;
                    }
                }
                if (isset($settings['role_permissions']) && is_array($settings['role_permissions'])) {
                    foreach ($settings['role_permissions'] as $role => $perms) {
                        if (!is_array($perms)) $settings['role_permissions'][$role] = (array)$perms;
                    }
                }

                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_ANON_KEY');
                $storeId = $request->header('X-Store-ID');
                if (!$storeId) { $storeId = $request->query('store'); }
                $storeId = is_string($storeId) ? trim($storeId) : '';
                if ($storeId === '' || $storeId === null) $storeId = 'default';
                $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
                $saved = false;
                if ($supabaseUrl && $supabaseKey) {
                    try {
                        $resp = null; $success = false;
                        for ($i=0; $i<3; $i++) {
                            try {
                                $resp = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Accept' => 'application/json',
            'Prefer' => 'resolution=merge-duplicates,return=representation',
            'X-Store-ID' => $storeId,

        ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings?on_conflict=id', [[
                                    'id' => $storeId,
                                    'store_name' => $settings['store_name'] ?? null,
                                    'settings' => $settings,
                                    'updated_at' => now()->toIso8601String(),
                                ]]);
                                if ($resp->successful()) { $success = true; break; }
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning('Supabase settings save failed', ['attempt'=>$i+1,'error'=>$e->getMessage()]);
                            }
                            usleep(150000 * ($i+1));
                        }
                        if ($success) { $saved = true; }
                    } catch (\Throwable $e) { /* fall back to DB */ }
                }

                if (!$saved) {
                    \Illuminate\Support\Facades\DB::table('pos_settings')->updateOrInsert(
                        ['id' => $storeId],
                        ['settings' => json_encode($settings), 'updated_at' => now()]
                    );
                }

                // Read-after-write verification from Supabase when available
                $fresh = $settings;
                if ($supabaseUrl && $supabaseKey) {
                    try {
                        $verify = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                            'id' => 'eq.' . $storeId,
                'select' => 'id,settings,updated_at',
                        ]);
                        if ($verify->ok()) {
                            $arr = $verify->json();
                            $row = (is_array($arr) && isset($arr[0])) ? $arr[0] : null;
                            if (is_array($row) && isset($row['settings']) && is_array($row['settings'])) {
                                $fresh = $row['settings'];
                            }
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Supabase verify after settings save failed', ['error'=>$e->getMessage()]);
                    }
                }

                \Illuminate\Support\Facades\Cache::put('pos_settings:' . $storeId, $fresh, now()->addYears(5));

                // Mask METRC keys in response
                if (is_array($fresh)) {
                    if (array_key_exists('metrc_user_key', $fresh)) {
                        $fresh['metrc_user_key'] = !empty($fresh['metrc_user_key']) ? '••••••••' : '';
                    }
                    if (array_key_exists('metrc_vendor_key', $fresh)) {
                        $fresh['metrc_vendor_key'] = !empty($fresh['metrc_vendor_key']) ? '••••••••' : '';
                    }
                }
                return response()->json(['success' => true, 'settings' => $fresh]);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        });

        // METRC settings (with permission check)
        Route::get('/metrc', function() {
            return response()->json([
                'facility_license' => config('services.metrc.facility_license'),
                'user_api_key' => config('services.metrc.user_key') ? '***' : null,
                'environment' => config('services.metrc.base_url'),
                'state' => 'OR',
                'enabled' => config('services.metrc.enabled')
            ]);
        })->middleware('permission:metrc:access');

        // Management operations (admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('/', [SettingsController::class, 'getSettings']);
            Route::post('/', [SettingsController::class, 'updateSettings']);
            Route::post('/reset', [SettingsController::class, 'resetSettings']);
            Route::get('/export', [SettingsController::class, 'exportSettings']);
            Route::post('/import', [SettingsController::class, 'importSettings']);
        });

        // Tax calculation (public within authenticated users)
        Route::post('/calculate-tax', [SettingsController::class, 'calculateTax']);

        // List available stores (ids/names) for switcher
        Route::get('/stores', function() {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
    $storeId = 'default';
    $stores = [];
            // Try Supabase first
            if ($supabaseUrl && $supabaseKey) {
                try {
                    $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $supabaseKey,
                'Authorization' => 'Bearer ' . $supabaseKey,
                'Accept' => 'application/json',
                'X-Store-ID' => $storeId,

            ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings', [
                'select' => 'id,settings,updated_at',
                        'order' => 'updated_at.desc'
                    ]);
                    if ($resp->ok()) {
                        $arr = $resp->json();
                        foreach ((array)$arr as $row) {
                            $id = (string)($row['id'] ?? '');
                            $name = $id;
                            if (isset($row['settings']) && is_array($row['settings']) && isset($row['settings']['store_name'])) {
                                $name = (string)$row['settings']['store_name'];
                            }
                            $stores[] = [
                                'id' => $id,
                                'name' => $name,
                                'updated_at' => $row['updated_at'] ?? null,
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Store list via Supabase failed', ['error'=>$e->getMessage()]);
                }
            }
            // Fallback to local DB table
            if (empty($stores)) {
                try {
                    $rows = \Illuminate\Support\Facades\DB::table('pos_settings')->select('id','settings','updated_at')->orderByDesc('updated_at')->limit(200)->get();
                    foreach ($rows as $r) {
                        $id = (string)$r->id;
                        $name = $id;
                        $settings = json_decode($r->settings ?? '{}', true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($settings['store_name'])) {
                            $name = (string)$settings['store_name'];
                        }
                        $stores[] = [ 'id'=>$id, 'name'=>$name, 'updated_at'=>$r->updated_at ];
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }
            return response()->json(['success' => true, 'stores' => $stores]);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Demo Routes (Development/Testing)
    |--------------------------------------------------------------------------
    */
    Route::get('/demo', [DemoController::class, 'demo'])
        ->middleware('role:admin');
});

/*
|--------------------------------------------------------------------------
| Catch-all and Error Handling
|--------------------------------------------------------------------------
*/

// API documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'api_name' => 'Cannabis POS API',
        'version' => '1.0.0',
        'documentation' => [
            'authentication' => [
                'POST /api/auth/login' => 'Login with email/password',
                'POST /api/auth/pin-login' => 'Login with employee PIN',
                'POST /api/auth/logout' => 'Logout current session',
                'GET /api/auth/me' => 'Get current user info'
            ],
            'products' => [
                'GET /api/products' => 'List all products',
                'POST /api/products' => 'Create new product',
                'GET /api/products/{id}' => 'Get product details',
                'PUT /api/products/{id}' => 'Update product'
            ],
            'metrc' => [
                'GET /api/metrc/test-connection' => 'Test METRC connection',
                'GET /api/metrc/packages' => 'Get all packages',
                'POST /api/metrc/packages/create' => 'Create new package'
            ],
            'sales' => [
                'GET /api/sales' => 'List sales',
                'POST /api/sales' => 'Create new sale',
                'GET /api/sales/{id}/receipt' => 'Get receipt'
            ]
        ],
        'permissions' => [
            'pos:access' => 'Access POS system',
            'products:read' => 'Read product data',
            'products:write' => 'Create/update products',
            'metrc:access' => 'Access METRC integration',
            'sales:create' => 'Process sales',
            'reports:read' => 'View reports'
        ],
        'roles' => [
            'admin' => 'Full system access',
            'manager' => 'Management operations',
            'cashier' => 'POS operations',
            'budtender' => 'POS and customer service',
            'inventory' => 'Inventory management'
        ]
    ]);
});

// Catch-all for undefined API routes
Route::fallback(function () {
    return response()->json([
        'error' => 'API endpoint not found',
        'message' => 'The requested API endpoint does not exist',
        'suggestion' => 'Check /api/docs for available endpoints'
    ], 404);
});

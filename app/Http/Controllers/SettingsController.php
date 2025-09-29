<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = $this->getCurrentSettings();
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Get current POS settings
     */
    public function getSettings()
    {
        try {
            $settings = $this->getCurrentSettings();

            // Mask sensitive fields in response
            $responseSettings = $settings;
            if (is_array($responseSettings)) {
                if (array_key_exists('metrc_user_key', $responseSettings)) {
                    $responseSettings['metrc_user_key'] = $responseSettings['metrc_user_key'] ? '••••••••' : '';
                }
                if (array_key_exists('metrc_vendor_key', $responseSettings)) {
                    $responseSettings['metrc_vendor_key'] = $responseSettings['metrc_vendor_key'] ? '••••••••' : '';
                }
            }
            return response()->json([
                'success' => true,
                'settings' => $responseSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching settings', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update POS settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Tax settings (optional to mirror Supabase; default/merge applied server-side)
            'sales_tax' => 'sometimes|numeric|min:0|max:100',
            'excise_tax' => 'sometimes|numeric|min:0|max:100',
            'cannabis_tax' => 'sometimes|numeric|min:0|max:100',
            'tax_inclusive' => 'sometimes|boolean',

            // POS/Receipt preferences (support both legacy and new keys)
            'auto_print_receipt' => 'sometimes|boolean',
            'receipt_autoprint' => 'sometimes|boolean',
            'receipt_show_tax_breakdown' => 'sometimes|boolean',
            'receipt_show_metrc' => 'sometimes|boolean',
            'receipt_show_loyalty' => 'sometimes|boolean',
            'receipt_show_qr_code' => 'sometimes|boolean',
            'default_receipt_printer' => 'sometimes|nullable|string|max:255',
            'receipt_paper_size' => 'sometimes|in:80mm,58mm,letter',
            'print_labels' => 'sometimes|boolean',
            'receipt_template' => 'sometimes|in:standard,detailed,minimal',

            // POS behavior
            'require_customer' => 'sometimes|boolean',
            'age_verification' => 'sometimes|boolean',
            'limit_enforcement' => 'sometimes|boolean',

            // Payment methods
            'accept_cash' => 'sometimes|boolean',
            'accept_debit' => 'sometimes|boolean',
            'accept_check' => 'sometimes|boolean',
            'round_to_nearest' => 'sometimes|boolean',

            // METRC integration
            'metrc_enabled' => 'sometimes|boolean',
            'metrc_user_key' => 'sometimes|nullable|string',
            'metrc_vendor_key' => 'sometimes|nullable|string',
            'metrc_facility' => 'sometimes|nullable|string',

            // Receipt & store info
            'receipt_footer' => 'sometimes|nullable|string|max:1000',
            'store_name' => 'required|string|max:255',
            'store_address' => 'sometimes|nullable|string|max:500',
            'store_phone' => 'sometimes|nullable|string|max:50',
            'store_email' => 'sometimes|nullable|email',
            'website' => 'sometimes|nullable|string|max:255',
            'store_manager' => 'sometimes|nullable|string|max:255',
            'license_number' => 'sometimes|nullable|string|max:255',

            // Exit labels and receipt category arrays
            'exit_label_categories' => 'sometimes|array',
            'exit_label_categories.*' => 'string',
            'receipt_categories_autoprint' => 'sometimes|array',
            'receipt_categories_autoprint.*' => 'string',

            // Pricing minimums
            'minimum_price_enabled' => 'sometimes|boolean',
            'minimum_price_amount' => 'sometimes|numeric|min:0',
            'minimum_price_categories' => 'sometimes|array',
            'minimum_price_categories.*' => 'string',

            // Display & inventory
            'inventory_view_mode' => 'sometimes|in:cards,list',
            'expandable_cart' => 'sometimes|boolean',

            // Auto-delete configuration
            'auto_delete_zero_quantity' => 'sometimes|boolean',
            'auto_delete_zero_days' => 'sometimes|integer|min:1|max:30',

            // Appearance
            'dark_mode' => 'sometimes|boolean',
            'theme_color' => 'sometimes|in:green,blue,purple,orange',
            'font_size' => 'sometimes|in:small,medium,large',
            'high_contrast' => 'sometimes|boolean',
            'reduce_motion' => 'sometimes|boolean',

            // Business hours
            'business_hours' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $settings = $request->all();

            // Normalize array-like inputs possibly sent as JSON strings
            $arrayFields = [
                'exit_label_categories',
                'receipt_categories_autoprint',
                'minimum_price_categories',
                'business_hours',
            ];
            foreach ($arrayFields as $field) {
                if (isset($settings[$field]) && is_string($settings[$field])) {
                    $decoded = json_decode($settings[$field], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $settings[$field] = $decoded;
                    }
                }
                if (isset($settings[$field]) && !is_array($settings[$field])) {
                    $settings[$field] = [];
                }
            }

            // Convert string boolean values
            $booleanFields = [
                'tax_inclusive', 'auto_print_receipt', 'receipt_autoprint', 'require_customer',
                'age_verification', 'limit_enforcement', 'accept_cash', 'receipt_show_tax_breakdown',
                'receipt_show_metrc', 'receipt_show_loyalty', 'receipt_show_qr_code',
                'accept_debit', 'accept_check', 'round_to_nearest', 'metrc_enabled',
                'minimum_price_enabled', 'expandable_cart', 'auto_delete_zero_quantity',
                'dark_mode', 'high_contrast', 'reduce_motion'
            ];
            foreach ($booleanFields as $field) {
                if (array_key_exists($field, $settings)) {
                    $settings[$field] = filter_var($settings[$field], FILTER_VALIDATE_BOOLEAN);
                }
            }

            // Ensure numeric types
            $numericFields = ['sales_tax','excise_tax','cannabis_tax','minimum_price_amount','auto_delete_zero_days'];
            foreach ($numericFields as $field) {
                if (isset($settings[$field])) {
                    $settings[$field] = is_numeric($settings[$field]) ? 0 + $settings[$field] : $settings[$field];
                }
            }

            // Alias: keep legacy and new key in sync
            if (isset($settings['receipt_autoprint']) && !isset($settings['auto_print_receipt'])) {
                $settings['auto_print_receipt'] = (bool)$settings['receipt_autoprint'];
            }
            if (isset($settings['auto_print_receipt']) && !isset($settings['receipt_autoprint'])) {
                $settings['receipt_autoprint'] = (bool)$settings['auto_print_receipt'];
            }

            // Preserve existing METRC keys if incoming payload contains masked values
            try {
                $existing = $this->getCurrentSettings();
                $maskPattern = '/^(?:[•*]+)$/u';
                foreach (['metrc_user_key','metrc_vendor_key'] as $k) {
                    if (isset($settings[$k]) && is_string($settings[$k]) && preg_match($maskPattern, trim($settings[$k]))) {
                        $settings[$k] = $existing[$k] ?? '';
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            // Handle per-user METRC user key (also persist globally per store)
            if (!empty($settings['metrc_user_key'])) {
                $user = auth()->user();
                if ($user && $user->employee) {
                    $emp = $user->employee;
                    $emp->metrc_api_key = $settings['metrc_user_key'];
                    $emp->save();
                }
                // Keep metrc_user_key in store-scoped settings as requested
            }

            // Compose final merged settings (defaults -> existing -> incoming)
            $existing = $this->getCurrentSettings();
            $defaults = $this->getDefaultSettings();
            $merged = array_replace_recursive($defaults, array_merge(is_array($existing)?$existing:[], is_array($settings)?$settings:[]));

            // Store remaining settings in cache with a long TTL
            Cache::put($this->cacheKeyForStore(), $merged, now()->addDays(30));

            // Persist to Supabase (store-scoped) with DB fallback
            try {
                $supabaseUrl = env('SUPABASE_URL');
                $supabaseKey = env('SUPABASE_ANON_KEY');
                $storeId = request()->header('X-Store-ID');
                $storeId = is_string($storeId) ? trim($storeId) : '';
                if ($storeId === '' || $storeId === null) $storeId = 'default';
                $storeId = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $storeId);
                $saved = false;
                if ($supabaseUrl && $supabaseKey) {
                    $resp = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Accept' => 'application/json',
                        'Prefer' => 'return=representation',
                        'X-Store-ID' => $storeId,
                    ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/pos_settings?on_conflict=id', [[
                        'id' => $storeId,
                        'settings' => $merged,
                        'updated_at' => now()->toIso8601String(),
                    ]]);
                    if ($resp->successful()) { $saved = true; }
                }
                if (!$saved) {
                    \Illuminate\Support\Facades\DB::table('pos_settings')->updateOrInsert(
                        ['id' => $storeId],
                        ['settings' => json_encode($merged), 'updated_at' => now()]
                    );
                }
            } catch (\Throwable $e) {
                // ignore persistence errors; cache still holds values
            }

            // Persist org-wide METRC settings to environment if provided
            if (!empty($settings['metrc_vendor_key'])) {
                $this->updateEnvVariable('METRC_VENDOR_KEY', $settings['metrc_vendor_key']);
            }
            if (!empty($settings['metrc_facility'])) {
                $this->updateEnvVariable('METRC_FACILITY', $settings['metrc_facility']);
            }

            // Log the settings update
            Log::info('POS settings updated', [
                'updated_settings' => array_keys($settings),
                'user_id' => auth()->id()
            ]);

            // Mask sensitive fields in response
            $responseSettings = $merged;
            if (is_array($responseSettings)) {
                if (array_key_exists('metrc_user_key', $responseSettings)) {
                    $responseSettings['metrc_user_key'] = $responseSettings['metrc_user_key'] ? '••••••••' : '';
                }
                if (array_key_exists('metrc_vendor_key', $responseSettings)) {
                    $responseSettings['metrc_vendor_key'] = $responseSettings['metrc_vendor_key'] ? '••••••••' : '';
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'settings' => $responseSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetSettings()
    {
        try {
            $defaultSettings = $this->getDefaultSettings();
            
            Cache::put($this->cacheKeyForStore(), $defaultSettings, now()->addDays(30));

            Log::info('POS settings reset to defaults', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults',
                'settings' => $defaultSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Error resetting settings', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax calculation for given amount
     */
    public function calculateTax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid amount provided'
            ], 400);
        }

        try {
            $settings = $this->getCurrentSettings();
            $amount = $request->amount;

            $salesTax = $amount * ($settings['sales_tax'] / 100);
            $exciseTax = $amount * ($settings['excise_tax'] / 100);
            $cannabisTax = $amount * ($settings['cannabis_tax'] / 100);
            
            $totalTax = $salesTax + $exciseTax + $cannabisTax;
            $totalAmount = $amount + $totalTax;

            return response()->json([
                'success' => true,
                'calculation' => [
                    'subtotal' => round($amount, 2),
                    'sales_tax' => round($salesTax, 2),
                    'excise_tax' => round($exciseTax, 2),
                    'cannabis_tax' => round($cannabisTax, 2),
                    'total_tax' => round($totalTax, 2),
                    'total_amount' => round($totalAmount, 2)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate tax: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current settings from cache or defaults
     */
    private function getCurrentSettings()
    {
        return Cache::get($this->cacheKeyForStore(), $this->getDefaultSettings());
    }

    /**
     * Get default settings
     */
    private function getDefaultSettings()
    {
        return [
            // Tax settings
            'sales_tax' => 0.0,
            'excise_tax' => 10.0,
            'cannabis_tax' => 17.0,
            'tax_inclusive' => false,

            // Store Information
            'store_name' => 'Cannabest POS',
            'store_address' => '',
            'store_phone' => '',
            'store_email' => '',
            'website' => '',
            'store_manager' => '',
            'license_number' => '',
            'receipt_footer' => "Thank you for your business!\nKeep receipt for returns and warranty.",

            // Exit Label Categories
            'exit_label_categories' => ['Flower','Pre-Rolls','Infused','Edibles','Concentrates','Vape Products','Tinctures','Topicals','Capsules','Beverages','Suppositories','Clones/Seeds','Immature Plants','Mature Plants','Hemp','Accessories','Inhalable Cannabinoids','Clones','Seeds'],

            // Receipt Printing
            'auto_print_receipt' => false,
            'receipt_autoprint' => false,
            'receipt_categories_autoprint' => [],
            'receipt_show_tax_breakdown' => true,
            'receipt_show_metrc' => true,
            'receipt_show_loyalty' => true,
            'receipt_show_qr_code' => false,
            'default_receipt_printer' => '',
            'receipt_paper_size' => '80mm',

            // POS behavior
            'require_customer' => true,
            'age_verification' => true,
            'limit_enforcement' => true,

            // Payment methods
            'accept_cash' => true,
            'accept_debit' => true,
            'accept_check' => false,
            'round_to_nearest' => false,

            // Pricing
            'minimum_price_enabled' => false,
            'minimum_price_amount' => 0.01,
            'minimum_price_categories' => [],

            // Display & Inventory
            'inventory_view_mode' => 'cards',
            'expandable_cart' => true,
            'weight_threshold' => 0,

            // Auto Delete
            'auto_delete_zero_quantity' => false,
            'auto_delete_zero_days' => 1,

            // METRC Integration
            'metrc_enabled' => true,
            'metrc_user_key' => env('METRC_USER_KEY', ''),
            'metrc_vendor_key' => env('METRC_VENDOR_KEY', ''),
            'metrc_facility' => env('METRC_FACILITY', ''),
            'metrc_auto_push_sales' => false,

            // Appearance
            'dark_mode' => false,
            'theme_color' => 'green',
            'font_size' => 'medium',
            'high_contrast' => false,
            'reduce_motion' => false,

            // Business Hours
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
    }

    /**
     * Determine current store ID from request header (defaults to 'default').
     */
    private function currentStoreIdFromRequest(): string
    {
        try {
            $sid = request()->header('X-Store-ID');
            $sid = is_string($sid) ? trim($sid) : '';
            if ($sid === '' || $sid === null) $sid = 'default';
            $sid = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $sid);
            if ($sid === 'defaultstore') $sid = 'default';
            return $sid ?: 'default';
        } catch (\Throwable $e) {
            return 'default';
        }
    }

    /**
     * Cache key for store-scoped POS settings
     */
    private function cacheKeyForStore(): string
    {
        return 'pos_settings:' . $this->currentStoreIdFromRequest();
    }

    /**
     * Update environment variable
     */
    private function updateEnvVariable($key, $value)
    {
        try {
            $envFile = base_path('.env');
            
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                
                if (strpos($envContent, "$key=") !== false) {
                    // Update existing variable
                    $envContent = preg_replace(
                        "/^$key=.*/m",
                        "$key=\"$value\"",
                        $envContent
                    );
                } else {
                    // Add new variable
                    $envContent .= "\n$key=\"$value\"";
                }
                
                file_put_contents($envFile, $envContent);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update environment variable', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Export settings as JSON
     */
    public function exportSettings()
    {
        try {
            $settings = $this->getCurrentSettings();
            
            // Remove sensitive data from export
            unset($settings['metrc_user_key']);
            unset($settings['metrc_vendor_key']);

            $filename = 'pos_settings_' . date('Y-m-d_H-i-s') . '.json';

            return response()->json($settings)
                ->header('Content-Disposition', "attachment; filename=\"$filename\"")
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import settings from JSON
     */
    public function importSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings_file' => 'required|file|mimes:json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file provided'
            ], 400);
        }

        try {
            $file = $request->file('settings_file');
            $content = file_get_contents($file->getPathname());
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format');
            }

            // Validate imported settings structure
            $defaultSettings = $this->getDefaultSettings();
            $validatedSettings = [];

            foreach ($defaultSettings as $key => $defaultValue) {
                if (isset($settings[$key])) {
                    $validatedSettings[$key] = $settings[$key];
                } else {
                    $validatedSettings[$key] = $defaultValue;
                }
            }

            Cache::put($this->cacheKeyForStore(), $validatedSettings, now()->addDays(30));

            Log::info('POS settings imported', [
                'imported_keys' => array_keys($settings),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings imported successfully',
                'settings' => $validatedSettings
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing settings', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to import settings: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /** Namespace for POS settings row */
    private const NS = 'pos';

    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = $this->getCurrentSettings();
        return view('settings.index', compact('settings'));
    }

    /**
     * Get current POS settings (JSON API)
     */
    public function getSettings()
    {
        try {
            $settings = $this->getCurrentSettings();

            return response()->json([
                'success'  => true,
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching settings', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update POS settings (JSON API)
     */
    public function updateSettings(Request $request)
    {
        // 1) Validate known fields (relaxed). Unknown fields allowed/merged.
        $rules = [
            // Taxes
            'sales_tax'                => 'required|numeric|min:0|max:100',
            'excise_tax'               => 'required|numeric|min:0|max:100',
            'cannabis_tax'             => 'required|numeric|min:0|max:100',
            'tax_inclusive'            => 'sometimes|boolean',

            // Store info
            'store_name'               => 'required|string|max:255',
            'store_manager'            => 'sometimes|nullable|string|max:255',
            'store_address'            => 'sometimes|nullable|string|max:500',
            'store_phone'              => 'sometimes|nullable|string|max:100',
            'store_email'              => 'sometimes|nullable|email|max:255',
            'website'                  => 'sometimes|nullable|url|max:255',
            'license_number'           => 'sometimes|nullable|string|max:255',

            // Receipt
            'receipt_footer'                 => 'sometimes|nullable|string|max:1000',
            'receipt_autoprint'              => 'sometimes|boolean',
            'receipt_categories_autoprint'   => 'sometimes|array',
            'receipt_categories_autoprint.*' => 'string',
            'receipt_show_tax_breakdown'     => 'sometimes|boolean',
            'receipt_show_metrc'             => 'sometimes|boolean',
            'receipt_show_loyalty'           => 'sometimes|boolean',
            'receipt_show_qr_code'           => 'sometimes|boolean',
            'default_receipt_printer'        => 'sometimes|nullable|string|max:255',
            'receipt_paper_size'             => 'sometimes|nullable|string|max:50',

            // Pricing
            'minimum_price_enabled'          => 'sometimes|boolean',
            'minimum_price_amount'           => 'sometimes|numeric|min:0',
            'minimum_price_categories'       => 'sometimes|array',
            'minimum_price_categories.*'     => 'string',

            // Display & inventory
            'inventory_view_mode'            => 'sometimes|in:cards,list',
            'expandable_cart'                => 'sometimes|boolean',
            'auto_delete_zero_quantity'      => 'sometimes|boolean',
            'auto_delete_zero_days'          => 'sometimes|integer|min:1|max:30',

            // Exit label categories
            'exit_label_categories'          => 'sometimes|array',
            'exit_label_categories.*'        => 'string',

            // Appearance
            'dark_mode'                      => 'sometimes|boolean',
            'theme_color'                    => 'sometimes|in:green,blue,purple,orange',
            'font_size'                      => 'sometimes|in:small,medium,large',
            'high_contrast'                  => 'sometimes|boolean',
            'reduce_motion'                  => 'sometimes|boolean',

            // Business hours
            'business_hours'                 => 'sometimes|array',
            'business_hours.*.day'           => 'required_with:business_hours|string',
            'business_hours.*.is_open'       => 'required_with:business_hours|boolean',
            'business_hours.*.open_time'     => 'required_if:business_hours.*.is_open,true|nullable|string',
            'business_hours.*.close_time'    => 'required_if:business_hours.*.is_open,true|nullable|string',

            // METRC
            'metrc_enabled'                  => 'sometimes|boolean',
            'metrc_user_key'                 => 'sometimes|nullable|string',
            'metrc_vendor_key'               => 'sometimes|nullable|string',
            'metrc_facility'                 => 'sometimes|nullable|string|max:255',

            // Legacy/extra POS prefs
            'require_customer'               => 'sometimes|boolean',
            'age_verification'               => 'sometimes|boolean',
            'limit_enforcement'              => 'sometimes|boolean',
            'accept_cash'                    => 'sometimes|boolean',
            'accept_debit'                   => 'sometimes|boolean',
            'accept_check'                   => 'sometimes|boolean',
            'round_to_nearest'               => 'sometimes|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // 2) Normalize/alias keys
            $payload = $validator->validated();

            $aliases = [
                'receipt_autoprint' => 'auto_print_receipt',
            ];
            foreach ($aliases as $from => $to) {
                if (array_key_exists($from, $payload)) {
                    $payload[$to] = $payload[$from];
                    unset($payload[$from]);
                }
            }

            // 3) Coerce boolean-ish fields
            $booleanFields = [
                'tax_inclusive',
                'auto_print_receipt',
                'require_customer',
                'age_verification',
                'limit_enforcement',
                'accept_cash',
                'accept_debit',
                'accept_check',
                'round_to_nearest',
                'metrc_enabled',
                'receipt_show_tax_breakdown',
                'receipt_show_metrc',
                'receipt_show_loyalty',
                'receipt_show_qr_code',
                'minimum_price_enabled',
                'expandable_cart',
                'auto_delete_zero_quantity',
                'dark_mode',
                'high_contrast',
                'reduce_motion',
            ];
            foreach ($booleanFields as $field) {
                if (array_key_exists($field, $payload)) {
                    $val = filter_var($payload[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($val !== null) $payload[$field] = $val;
                }
            }

            // 4) Merge with existing (preserve missing fields)
            $current = $this->getCurrentSettings();
            $merged  = array_replace_recursive($current, $payload);

            // Ensure array types
            foreach (['receipt_categories_autoprint','minimum_price_categories','exit_label_categories','business_hours'] as $arrKey) {
                if (!isset($merged[$arrKey]) || !is_array($merged[$arrKey])) {
                    $merged[$arrKey] = [];
                }
            }

            // 5) Persist to DB (and cache-bust)
            Settings::updateOrCreate(
                ['namespace' => self::NS],
                ['data' => $merged]
            );
            Cache::put('pos_settings', $merged, now()->addDays(30));

            // 6) Optionally reflect sensitive METRC creds into .env (best-effort)
            if (!empty($merged['metrc_user_key']))   $this->updateEnvVariable('METRC_USER_KEY', $merged['metrc_user_key']);
            if (!empty($merged['metrc_vendor_key'])) $this->updateEnvVariable('METRC_VENDOR_KEY', $merged['metrc_vendor_key']);
            if (!empty($merged['metrc_facility']))   $this->updateEnvVariable('METRC_FACILITY', $merged['metrc_facility']);

            Log::info('POS settings updated', ['updated_keys' => array_keys($payload), 'user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Settings updated successfully',
                'settings' => $merged,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating settings', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetSettings()
    {
        try {
            $default = $this->getDefaultSettings();

            Settings::updateOrCreate(
                ['namespace' => self::NS],
                ['data' => $default]
            );
            Cache::put('pos_settings', $default, now()->addDays(30));

            Log::info('POS settings reset to defaults', ['user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Settings reset to defaults',
                'settings' => $default,
            ]);
        } catch (\Exception $e) {
            Log::error('Error resetting settings', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tax calculation
     */
    public function calculateTax(Request $request)
    {
        $validator = Validator::make($request->all(), ['amount' => 'required|numeric|min:0']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid amount provided'], 400);
        }

        try {
            $settings = $this->getCurrentSettings();
            $amount   = (float) $request->amount;

            $salesTax    = $amount * ($settings['sales_tax'] / 100);
            $exciseTax   = $amount * ($settings['excise_tax'] / 100);
            $cannabisTax = $amount * ($settings['cannabis_tax'] / 100);

            $totalTax    = $salesTax + $exciseTax + $cannabisTax;
            $totalAmount = $amount + $totalTax;

            return response()->json([
                'success'     => true,
                'calculation' => [
                    'subtotal'      => round($amount, 2),
                    'sales_tax'     => round($salesTax, 2),
                    'excise_tax'    => round($exciseTax, 2),
                    'cannabis_tax'  => round($cannabisTax, 2),
                    'total_tax'     => round($totalTax, 2),
                    'total_amount'  => round($totalAmount, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to calculate tax: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export settings (sanitized)
     */
    public function exportSettings()
    {
        try {
            $settings = $this->getCurrentSettings();
            unset($settings['metrc_user_key'], $settings['metrc_vendor_key']);

            $filename = 'pos_settings_' . date('Y-m-d_H-i-s') . '.json';
            return response()->json($settings)
                ->header('Content-Disposition', "attachment; filename=\"$filename\"")
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to export settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import settings from JSON file
     */
    public function importSettings(Request $request)
    {
        $validator = Validator::make($request->all(), ['settings_file' => 'required|file|mimes:json']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid file provided'], 400);
        }

        try {
            $file     = $request->file('settings_file');
            $content  = file_get_contents($file->getPathname());
            $incoming = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format');
            }

            $default  = $this->getDefaultSettings();
            $merged   = array_replace_recursive($default, (array) $incoming);

            Settings::updateOrCreate(
                ['namespace' => self::NS],
                ['data' => $merged]
            );
            Cache::put('pos_settings', $merged, now()->addDays(30));

            Log::info('POS settings imported', ['imported_keys' => array_keys((array) $incoming), 'user_id' => auth()->id()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Settings imported successfully',
                'settings' => $merged,
            ]);
        } catch (\Exception $e) {
            Log::error('Error importing settings', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to import settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Read current settings from cache or DB; seed defaults if missing
     */
    private function getCurrentSettings(): array
    {
        return Cache::remember('pos_settings', now()->addDays(30), function () {
            $row = Settings::where('namespace', self::NS)->first();

            if (!$row) {
                $defaults = $this->getDefaultSettings();
                Settings::updateOrCreate(
                    ['namespace' => self::NS],
                    ['data' => $defaults]
                );
                return $defaults;
            }

            // Always ensure array output
            $data = $row->data;
            return is_array($data) ? $data : (array) json_decode($data, true);
        });
    }


    /**
     * Default settings
     */
    private function getDefaultSettings(): array
    {
        return [
            // Tax settings
            'sales_tax'      => 20.0,
            'excise_tax'     => 10.0,
            'cannabis_tax'   => 17.0,
            'tax_inclusive'  => false,

            // POS preferences
            'auto_print_receipt' => true,
            'require_customer'   => true,
            'age_verification'   => true,
            'limit_enforcement'  => true,

            // Payment methods
            'accept_cash'     => true,
            'accept_debit'    => true,
            'accept_check'    => false,
            'round_to_nearest'=> false,

            // METRC integration
            'metrc_enabled'   => true,
            'metrc_user_key'  => env('METRC_USER_KEY', ''),
            'metrc_vendor_key'=> env('METRC_VENDOR_KEY', ''),
            'metrc_facility'  => env('METRC_FACILITY', ''),

            // Receipt & store info
            'receipt_footer'  => "Thank you for your business!\nKeep receipt for returns and warranty.",
            'store_name'      => 'Cannabis POS',
            'store_manager'   => '',
            'store_address'   => '',
            'store_phone'     => '',
            'store_email'     => '',
            'website'         => '',
            'license_number'  => '',

            // UI options
            'receipt_categories_autoprint' => [],
            'receipt_show_tax_breakdown'   => true,
            'receipt_show_metrc'           => true,
            'receipt_show_loyalty'         => true,
            'receipt_show_qr_code'         => false,
            'default_receipt_printer'      => '',
            'receipt_paper_size'           => '80mm',

            // Pricing
            'minimum_price_enabled'  => false,
            'minimum_price_amount'   => 0.01,
            'minimum_price_categories'=> [],

            // Display & inventory
            'inventory_view_mode'    => 'cards',
            'expandable_cart'        => true,
            'auto_delete_zero_quantity' => false,
            'auto_delete_zero_days'  => 1,

            // Exit labels
            'exit_label_categories'  => ['Flower', 'Pre-Rolls', 'Concentrates', 'Edibles'],

            // Appearance
            'dark_mode'     => false,
            'theme_color'   => 'green',
            'font_size'     => 'medium',
            'high_contrast' => false,
            'reduce_motion' => false,

            // Business hours
            'business_hours' => [
                ['day' => 'Monday',    'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                ['day' => 'Tuesday',   'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                ['day' => 'Wednesday', 'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                ['day' => 'Thursday',  'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                ['day' => 'Friday',    'is_open' => true, 'open_time' => '09:00', 'close_time' => '21:00'],
                ['day' => 'Saturday',  'is_open' => true, 'open_time' => '10:00', 'close_time' => '20:00'],
                ['day' => 'Sunday',    'is_open' => true, 'open_time' => '11:00', 'close_time' => '19:00'],
            ],
        ];
    }

    /**
     * Update environment variable (best effort)
     */
    private function updateEnvVariable($key, $value): void
    {
        try {
            $envFile = base_path('.env');
            if (!file_exists($envFile)) return;

            $env = file_get_contents($envFile);
            $line = $key . '="'; // avoid partial matches
            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", $key . '="' . addslashes($value) . '"', $env);
            } else {
                $env .= "\n" . $key . '="' . addslashes($value) . '"';
            }
            file_put_contents($envFile, $env);
        } catch (\Exception $e) {
            Log::warning('Failed to update environment variable', ['key' => $key, 'error' => $e->getMessage()]);
        }
    }
}

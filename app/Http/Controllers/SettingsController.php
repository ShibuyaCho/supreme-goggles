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

            return response()->json([
                'success' => true,
                'settings' => $settings
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
            // Tax settings
            'sales_tax' => 'required|numeric|min:0|max:100',
            'excise_tax' => 'required|numeric|min:0|max:100',
            'cannabis_tax' => 'required|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',

            // POS preferences
            'auto_print_receipt' => 'boolean',
            'require_customer' => 'boolean',
            'age_verification' => 'boolean',
            'limit_enforcement' => 'boolean',

            // Payment methods
            'accept_cash' => 'boolean',
            'accept_debit' => 'boolean',
            'accept_check' => 'boolean',
            'round_to_nearest' => 'boolean',

            // METRC integration
            'metrc_enabled' => 'boolean',
            'metrc_user_key' => 'nullable|string',
            'metrc_vendor_key' => 'nullable|string',
            'metrc_facility' => 'nullable|string',

            // Receipt settings
            'receipt_footer' => 'nullable|string|max:1000',
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500'
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
            
            // Convert string boolean values
            $booleanFields = [
                'tax_inclusive', 'auto_print_receipt', 'require_customer',
                'age_verification', 'limit_enforcement', 'accept_cash',
                'accept_debit', 'accept_check', 'round_to_nearest', 'metrc_enabled'
            ];

            foreach ($booleanFields as $field) {
                if (isset($settings[$field])) {
                    $settings[$field] = filter_var($settings[$field], FILTER_VALIDATE_BOOLEAN);
                }
            }

            // Store settings in cache with a long TTL
            Cache::put('pos_settings', $settings, now()->addDays(30));

            // Also store in environment variables for METRC keys (if provided)
            if (!empty($settings['metrc_user_key'])) {
                $this->updateEnvVariable('METRC_USER_KEY', $settings['metrc_user_key']);
            }
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

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'settings' => $settings
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
            
            Cache::put('pos_settings', $defaultSettings, now()->addDays(30));

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
        return Cache::get('pos_settings', $this->getDefaultSettings());
    }

    /**
     * Get default settings
     */
    private function getDefaultSettings()
    {
        return [
            // Tax settings
            'sales_tax' => 20.0,
            'excise_tax' => 10.0,
            'cannabis_tax' => 17.0,
            'tax_inclusive' => false,

            // POS preferences
            'auto_print_receipt' => true,
            'require_customer' => true,
            'age_verification' => true,
            'limit_enforcement' => true,

            // Payment methods
            'accept_cash' => true,
            'accept_debit' => true,
            'accept_check' => false,
            'round_to_nearest' => false,

            // METRC integration
            'metrc_enabled' => true,
            'metrc_user_key' => env('METRC_USER_KEY', ''),
            'metrc_vendor_key' => env('METRC_VENDOR_KEY', ''),
            'metrc_facility' => env('METRC_FACILITY', ''),

            // Receipt settings
            'receipt_footer' => "Thank you for your business!\nKeep receipt for returns and warranty.",
            'store_name' => 'Cannabis POS',
            'store_address' => ''
        ];
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

            Cache::put('pos_settings', $validatedSettings, now()->addDays(30));

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

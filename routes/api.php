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

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/pin-login', [AuthController::class, 'pinLogin']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/self-register', [AuthController::class, 'selfRegister']);
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
        Route::post('/verify-metrc', [AuthController::class, 'verifyMetrc'])
            ->middleware('permission:metrc:access');
        Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('api.verification.send');
    });

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
        ->middleware('role:admin');

    Route::prefix('metrc')->middleware('permission:metrc:access')->group(function () {
        Route::get('/status', function() {
            $svc = app(\App\Services\MetrcService::class);
            return response()->json([
                'connected' => $svc->isConfigured(),
                'facility' => env('METRC_FACILITY') ?: (\Illuminate\Support\Facades\Cache::get('pos_settings')['metrc_facility'] ?? null),
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
        Route::get('/overview', [AnalyticsController::class, 'getOverview']);
        Route::get('/products', [AnalyticsController::class, 'getProductAnalytics']);
        Route::get('/customers', [AnalyticsController::class, 'getCustomerAnalytics']);
        Route::get('/inventory', [AnalyticsController::class, 'getInventoryAnalytics']);
        Route::get('/employees', [AnalyticsController::class, 'getEmployeeAnalytics']);
        Route::get('/aspd', [AnalyticsController::class, 'getASPDAnalytics']);
        Route::get('/end-of-day', [AnalyticsController::class, 'getEndOfDayReport']);
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
            $cached = \Illuminate\Support\Facades\Cache::get('pos_settings');
            if (!$cached) {
                try {
                    $row = \Illuminate\Support\Facades\DB::table('pos_settings')->where('id','default')->first();
                    if ($row && isset($row->settings)) {
                        $decoded = json_decode($row->settings, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $cached = $decoded;
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
            return response()->json([
                'success' => true,
                'settings' => $settings,
                'tax_rate' => $settings['sales_tax'] ?? 20.0,
                'medical_tax_rate' => 0.0,
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
            try {
                $settings = $request->all();
                foreach (['exit_label_categories','receipt_categories_autoprint','minimum_price_categories','role_permissions'] as $field) {
                    if (isset($settings[$field]) && is_string($settings[$field])) {
                        $decoded = json_decode($settings[$field], true);
                        if (json_last_error() === JSON_ERROR_NONE) $settings[$field] = $decoded;
                    }
                }
                \Illuminate\Support\Facades\Cache::put('pos_settings', $settings, now()->addDays(30));
                try {
                    \Illuminate\Support\Facades\DB::table('pos_settings')->updateOrInsert(
                        ['id' => 'default'],
                        ['settings' => json_encode($settings), 'updated_at' => now()]
                    );
                } catch (\Throwable $e) {}
                return response()->json(['success' => true, 'settings' => $settings]);
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

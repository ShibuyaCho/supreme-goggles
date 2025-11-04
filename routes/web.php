<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderQueueController;
use App\Http\Controllers\PriceTiersController;
use App\Http\Controllers\ProductActionsController;
use App\Http\Controllers\MetrcSettingsController;

/*
|--------------------------------------------------------------------------
| Auth / Guest (web session)
|--------------------------------------------------------------------------
| Use the session-based 'web' guard here.
*/
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth:web');

/*
|--------------------------------------------------------------------------
| Root & Legacy home
|--------------------------------------------------------------------------
| If authenticated, drop users straight into POS; otherwise to login.
*/
Route::get('/', function () {
    return auth('web')->check()
        ? redirect()->route('pos.index')
        : redirect()->route('login');
})->name('root');

Route::get('/home', function () {
    return redirect()->route('pos.index');
})->name('home');

/*
|--------------------------------------------------------------------------
| POS (Blade pages; session auth)
|--------------------------------------------------------------------------
| Session-guarded. Avoid Sanctum here to prevent login/POS loops.
*/
Route::prefix('pos')
    ->name('pos.')
    ->middleware(['auth:web','role:admin|manager|cashier|budtender'])
    ->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');

        Route::post('/new-sale',        [POSController::class, 'newSale'])->name('new-sale');
        Route::post('/add-to-cart',     [POSController::class, 'addToCart'])->name('add-to-cart');
        Route::patch('/cart/{item}',    [POSController::class, 'updateCart'])->name('update-cart');
        Route::delete('/cart/{item}',   [POSController::class, 'removeFromCart'])->name('remove-from-cart');
        Route::delete('/cart',          [POSController::class, 'clearCart'])->name('clear-cart');
        Route::post('/apply-discount',  [POSController::class, 'applyDiscount'])->name('apply-discount');

        Route::post('/save-sale',       [POSController::class, 'saveSale'])
            ->name('save-sale')->middleware('role:admin|manager|cashier');
        Route::post('/load-sale/{id}',  [POSController::class, 'loadSale'])
            ->name('load-sale')->middleware('role:admin|manager|cashier');
        Route::post('/process-payment', [POSController::class, 'processPayment'])
            ->name('process-payment')->middleware('role:admin|manager|cashier');

        // POS Utilities
        Route::post('/check-limits',                [POSController::class, 'checkOregonLimits'])->name('check-limits');
        Route::post('/print-barcode/{product}',     [ProductActionsController::class, 'printBarcode'])->name('print-barcode');
        Route::post('/print-exit-label/{product}',  [ProductActionsController::class, 'printExitLabel'])->name('print-exit-label');
        Route::get('/metrc-info/{product}',         [ProductActionsController::class, 'getMetrcDetails'])->name('metrc-info');
    });

/*
|--------------------------------------------------------------------------
| Products (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('products')->name('products.')
    ->middleware(['auth:web','role:admin|manager|cashier|budtender|inventory'])
    ->group(function () {
        Route::get('/',          [ProductsController::class, 'index'])->name('index');
        Route::get('/{product}', [ProductsController::class, 'show'])->name('show');

        Route::middleware('role:admin|manager|inventory')->group(function () {
            Route::get('/create',         [ProductsController::class, 'create'])->name('create');
            Route::post('/',              [ProductsController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductsController::class, 'edit'])->name('edit');
            Route::patch('/{product}',    [ProductsController::class, 'update'])->name('update');
            Route::delete('/{product}',   [ProductsController::class, 'destroy'])->name('destroy');

            Route::post('/{product}/transfer-room',  [ProductsController::class, 'transferRoom'])->name('transfer-room');
            Route::post('/{product}/adjust-quantity',[ProductsController::class, 'adjustQuantity'])->name('adjust-quantity');
            Route::post('/{product}/update-pricing', [ProductsController::class, 'updatePricing'])->name('update-pricing');
        });

        Route::get('/{product}/barcode', [ProductsController::class, 'generateBarcode'])->name('barcode');
        Route::get('/{product}/label',   [ProductsController::class, 'generateLabel'])->name('label');

        Route::middleware('role:admin|manager|inventory')->group(function () {
            Route::post('/bulk-transfer', [ProductsController::class, 'bulkTransfer'])->name('bulk-transfer');
            Route::post('/bulk-pricing',  [ProductsController::class, 'bulkPricing'])->name('bulk-pricing');
            Route::post('/bulk-delete',   [ProductsController::class, 'bulkDelete'])->name('bulk-delete');
        });

        Route::get('/export', [ProductsController::class, 'export'])->name('export');
        Route::post('/import', [ProductsController::class, 'import'])->name('import')->middleware('role:admin|manager|inventory');

        Route::get('/search/{query}',      [ProductsController::class, 'search'])->name('search');
        Route::get('/category/{category}', [ProductsController::class, 'byCategory'])->name('by-category');
        Route::get('/room/{room}',         [ProductsController::class, 'byRoom'])->name('by-room');
        Route::get('/low-stock',           [ProductsController::class, 'lowStock'])->name('low-stock')->middleware('role:admin|manager|inventory');
        Route::get('/out-of-stock',        [ProductsController::class, 'outOfStock'])->name('out-of-stock')->middleware('role:admin|manager|inventory');
        Route::get('/expiring',            [ProductsController::class, 'expiring'])->name('expiring')->middleware('role:admin|manager|inventory');
    });

/*
|--------------------------------------------------------------------------
| Customers (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('customers')->name('customers.')
    ->middleware(['auth:web','role:admin|manager|cashier|budtender'])
    ->group(function () {
        Route::get('/',               [CustomersController::class, 'index'])->name('index');
        Route::get('/{customer}',     [CustomersController::class, 'show'])->name('show');
        Route::get('/search/{query}', [CustomersController::class, 'search'])->name('search');
        Route::get('/export',         [CustomersController::class, 'export'])->name('export');
        Route::get('/loyalty-members',[CustomersController::class, 'loyaltyMembers'])->name('loyalty-members');
        Route::get('/medical-patients',[CustomersController::class, 'medicalPatients'])->name('medical-patients');
        Route::get('/high-value',     [CustomersController::class, 'highValue'])->name('high-value');

        Route::middleware('role:admin|manager|cashier')->group(function () {
            Route::get('/create',           [CustomersController::class, 'create'])->name('create');
            Route::post('/',                [CustomersController::class, 'store'])->name('store');
            Route::get('/{customer}/edit',  [CustomersController::class, 'edit'])->name('edit');
            Route::patch('/{customer}',     [CustomersController::class, 'update'])->name('update');
            Route::delete('/{customer}',    [CustomersController::class, 'destroy'])->name('destroy');

            Route::post('/{customer}/add-loyalty-points', [CustomersController::class, 'addLoyaltyPoints'])->name('add-loyalty-points');
            Route::post('/{customer}/redeem-points',      [CustomersController::class, 'redeemPoints'])->name('redeem-points');
            Route::post('/{customer}/update-tier',        [CustomersController::class, 'updateTier'])->name('update-tier');
            Route::post('/{customer}/start-sale',         [CustomersController::class, 'startSale'])->name('start-sale');
        });

        Route::get('/{customer}/purchase-history', [CustomersController::class, 'purchaseHistory'])->name('purchase-history');
    });

/*
|--------------------------------------------------------------------------
| Sales (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('sales')->name('sales.')
    ->middleware(['auth:web','role:admin|manager|cashier'])
    ->group(function () {
        Route::get('/',                  [SalesController::class, 'index'])->name('index');
        Route::get('/{sale}',            [SalesController::class, 'show'])->name('show');
        Route::get('/{sale}/receipt',    [SalesController::class, 'receipt'])->name('receipt');
        Route::post('/{sale}/reprint-receipt', [SalesController::class, 'reprintReceipt'])->name('reprint-receipt');

        Route::middleware('role:admin|manager')->group(function () {
            Route::post('/{sale}/void',   [SalesController::class, 'void'])->name('void');
            Route::post('/{sale}/refund', [SalesController::class, 'refund'])->name('refund');
        });

        Route::get('/report/daily',   [SalesController::class, 'dailyReport'])->name('daily-report');
        Route::get('/report/weekly',  [SalesController::class, 'weeklyReport'])->name('weekly-report');
        Route::get('/report/monthly', [SalesController::class, 'monthlyReport'])->name('monthly-report');
        Route::get('/report/custom',  [SalesController::class, 'customReport'])->name('custom-report');

        Route::get('/export',         [SalesController::class, 'export'])->name('export');
        Route::get('/export/metrc',   [SalesController::class, 'exportMetrc'])->name('export-metrc')->middleware('role:admin|manager');
    });

/*
|--------------------------------------------------------------------------
| Analytics / Reports (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('analytics')->name('analytics.')
    ->middleware(['auth:web','role:admin|manager'])
    ->group(function () {
        Route::get('/',                [AnalyticsController::class, 'index'])->name('index');
        Route::get('/dashboard',       [AnalyticsController::class, 'dashboard'])->name('dashboard');

        Route::get('/export-overview', [AnalyticsController::class, 'exportOverview'])->name('export-overview');

        Route::get('/sales/overview',      [AnalyticsController::class, 'salesOverview'])->name('sales-overview');
        Route::get('/sales/trends',        [AnalyticsController::class, 'salesTrends'])->name('sales-trends');
        Route::get('/sales/by-category',   [AnalyticsController::class, 'salesByCategory'])->name('sales-by-category');
        Route::get('/sales/by-employee',   [AnalyticsController::class, 'salesByEmployee'])->name('sales-by-employee');
        Route::get('/sales/by-time',       [AnalyticsController::class, 'salesByTime'])->name('sales-by-time');

        Route::get('/products/performance', [AnalyticsController::class, 'productPerformance'])->name('product-performance');
        Route::get('/products/top-selling', [AnalyticsController::class, 'topSellingProducts'])->name('top-selling');
        Route::get('/products/slow-moving', [AnalyticsController::class, 'slowMoving'])->name('slow-moving');
        Route::get('/products/margin-analysis', [AnalyticsController::class, 'marginAnalysis'])->name('margin-analysis');

        Route::get('/customers/overview',       [AnalyticsController::class, 'customerOverview'])->name('customer-overview');
        Route::get('/customers/retention',      [AnalyticsController::class, 'customerRetention'])->name('customer-retention');
        Route::get('/customers/lifetime-value', [AnalyticsController::class, 'customerLifetimeValue'])->name('customer-lifetime-value');
        Route::get('/customers/loyalty-program',[AnalyticsController::class, 'loyaltyProgramAnalytics'])->name('loyalty-program');

        Route::get('/inventory/turnover',    [AnalyticsController::class, 'inventoryTurnover'])->name('inventory-turnover');
        Route::get('/inventory/valuation',   [AnalyticsController::class, 'inventoryValuation'])->name('inventory-valuation');
        Route::get('/inventory/forecasting', [AnalyticsController::class, 'inventoryForecasting'])->name('inventory-forecasting');
    });

Route::prefix('reports')->name('reports.')
    ->middleware(['auth:web','role:admin|manager'])
    ->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');

        // Financial
        Route::get('/financial/daily-sales',   [ReportsController::class, 'dailySales'])->name('daily-sales');
        Route::get('/financial/weekly-sales',  [ReportsController::class, 'weeklySales'])->name('weekly-sales');
        Route::get('/financial/monthly-sales', [ReportsController::class, 'monthlySales'])->name('monthly-sales');
        Route::get('/financial/tax-report',    [ReportsController::class, 'taxReport'])->name('tax-report');
        Route::get('/financial/payment-methods',[ReportsController::class, 'paymentMethods'])->name('payment-methods');

        // Inventory
        Route::get('/inventory/current-stock',    [ReportsController::class, 'currentStock'])->name('current-stock');
        Route::get('/inventory/low-stock',        [ReportsController::class, 'lowStock'])->name('low-stock');
        Route::get('/inventory/expiring-products',[ReportsController::class, 'expiringProducts'])->name('expiring-products');
        Route::get('/inventory/movement',         [ReportsController::class, 'inventoryMovement'])->name('inventory-movement');
        Route::get('/inventory/valuation',        [ReportsController::class, 'inventoryValuation'])->name('inventory-valuation');

        // Compliance
        Route::get('/compliance/metrc-sync',     [ReportsController::class, 'metrcSync'])->name('metrc-sync');
        Route::get('/compliance/audit-trail',    [ReportsController::class, 'auditTrail'])->name('audit-trail');
        Route::get('/compliance/oregon-limits',  [ReportsController::class, 'oregonLimits'])->name('oregon-limits');

        // Customers
        Route::get('/customers/overview',        [ReportsController::class, 'customerOverview'])->name('customer-overview');
        Route::get('/customers/loyalty',         [ReportsController::class, 'loyaltyReport'])->name('loyalty-report');
        Route::get('/customers/medical-patients',[ReportsController::class, 'medicalPatients'])->name('medical-patients');
    });

/*
|--------------------------------------------------------------------------
| Settings (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('settings')->name('settings.')
    ->middleware(['auth:web','role:admin'])
    ->group(function () {
        Route::get('/',         [SettingsController::class, 'index'])->name('index');
        Route::post('/',        [SettingsController::class, 'update'])->name('update');

        Route::get('/tax',      [SettingsController::class, 'tax'])->name('tax');
        Route::post('/tax',     [SettingsController::class, 'updateTax'])->name('update-tax');

        Route::get('/pos',      [SettingsController::class, 'pos'])->name('pos');
        Route::post('/pos',     [SettingsController::class, 'updatePos'])->name('update-pos');

        Route::get('/printers',      [SettingsController::class, 'printers'])->name('printers');
        Route::post('/printers',     [SettingsController::class, 'updatePrinters'])->name('update-printers');
        Route::post('/printers/test',[SettingsController::class, 'testPrinter'])->name('test-printer');

        Route::get('/metrc',       [SettingsController::class, 'metrc'])->name('metrc');
        Route::post('/metrc',      [SettingsController::class, 'updateMetrc'])->name('update-metrc');
        Route::post('/metrc/test', [SettingsController::class, 'testMetrc'])->name('test-metrc');

        Route::get('/backup',          [SettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/create',  [SettingsController::class, 'createBackup'])->name('create-backup');
        Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('restore-backup');
    });

/*
|--------------------------------------------------------------------------
| Payment (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('payment')->name('payment.')
    ->middleware(['auth:web'])
    ->group(function () {
        Route::post('/process',              [PaymentController::class, 'process'])->name('process')->middleware('role:admin|manager|cashier');
        Route::post('/void/{transaction}',   [PaymentController::class, 'void'])->name('void')->middleware('role:admin|manager');
        Route::post('/refund/{transaction}', [PaymentController::class, 'refund'])->name('refund')->middleware('role:admin|manager');
        Route::get('/batch-report',          [PaymentController::class, 'batchReport'])->name('batch-report')->middleware('role:admin|manager');
        Route::post('/close-batch',          [PaymentController::class, 'closeBatch'])->name('close-batch')->middleware('role:admin|manager');
    });

/*
|--------------------------------------------------------------------------
| Order Queue (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('order-queue')->name('order-queue.')
    ->middleware(['auth:web','role:admin|manager|cashier'])
    ->group(function () {
        Route::get('/',                   [OrderQueueController::class, 'index'])->name('index');
        Route::get('/{order}',            [OrderQueueController::class, 'show'])->name('show');
        Route::post('/{order}/fulfill',   [OrderQueueController::class, 'fulfill'])->name('fulfill');
        Route::post('/{order}/cancel',    [OrderQueueController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/partial-fulfill', [OrderQueueController::class, 'partialFulfill'])->name('partial-fulfill');
    });

/*
|--------------------------------------------------------------------------
| Price Tiers (Blade; session auth)
|--------------------------------------------------------------------------
*/
Route::prefix('price-tiers')->name('price-tiers.')
    ->middleware(['auth:web','role:admin|manager'])
    ->group(function () {
        Route::get('/',                 [PriceTiersController::class, 'index'])->name('index');
        Route::post('/',                [PriceTiersController::class, 'store'])->name('store');
        Route::patch('/{priceTier}',    [PriceTiersController::class, 'update'])->name('update');
        Route::delete('/{priceTier}',   [PriceTiersController::class, 'destroy'])->name('destroy');
        Route::post('/apply-to-products',[PriceTiersController::class, 'applyToProducts'])->name('apply-to-products');
    });

/*
|--------------------------------------------------------------------------
| API (XHR) — Sanctum (cookie-based)
|--------------------------------------------------------------------------
| IMPORTANT:
| - Includes 'api' group so EnsureFrontendRequestsAreStateful + throttle apply.
| - Frontend must GET /sanctum/csrf-cookie before state-changing requests.
*/
Route::prefix('api')->name('api.')
    ->middleware(['api','auth:sanctum'])
    ->group(function () {
        // quick auth check for the SPA
        Route::get('/auth/me', fn() => auth()->user());

        Route::get('/products/search',       [ProductsController::class, 'apiSearch'])->name('products.search');
        Route::get('/customers/search',      [CustomersController::class, 'apiSearch'])->name('customers.search');
        Route::get('/sales/recent',          [SalesController::class, 'recentSales'])->name('sales.recent');
        Route::get('/analytics/quick-stats', [AnalyticsController::class, 'quickStats'])->name('analytics.quick-stats');
        Route::post('/cart/validate',        [POSController::class, 'validateCart'])->name('cart.validate');
        Route::get('/metrc/product/{tag}',   [POSController::class, 'getMetrcProduct'])->name('metrc.product');
    });

Route::prefix('settings/metrc')->name('settings.metrc.')
    ->middleware(['auth:web','role:admin'])
    ->group(function () {
        Route::get('/', [MetrcSettingsController::class, 'index'])->name('index');
        Route::post('/', [MetrcSettingsController::class, 'store'])->name('store');
        Route::post('/{id}/test', [MetrcSettingsController::class, 'test'])->name('test');
        Route::post('/{id}/activate', [MetrcSettingsController::class, 'activate'])->name('activate');
    });

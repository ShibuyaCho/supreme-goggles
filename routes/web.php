<?php

use Illuminate\Support\Facades\Route;
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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main POS route
Route::get('/', function () {
    return view('pos-main.index');
});

// Redirect legacy routes
Route::get('/pos', function () {
    return redirect('/');
});

// Point of Sale Routes
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [POSController::class, 'index'])->name('index');
    Route::post('/new-sale', [POSController::class, 'newSale'])->name('new-sale');
    Route::post('/add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
    Route::delete('/cart/{item}', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::patch('/cart/{item}', [POSController::class, 'updateCart'])->name('update-cart');
    Route::delete('/cart', [POSController::class, 'clearCart'])->name('clear-cart');
    Route::post('/apply-discount', [POSController::class, 'applyDiscount'])->name('apply-discount');
    Route::post('/save-sale', [POSController::class, 'saveSale'])->name('save-sale');
    Route::get('/saved-sales', [POSController::class, 'savedSales'])->name('saved-sales');
    Route::post('/load-sale/{id}', [POSController::class, 'loadSale'])->name('load-sale');
    Route::post('/process-payment', [POSController::class, 'processPayment'])->name('process-payment');
    
    // POS Utilities
    Route::post('/check-limits', [POSController::class, 'checkOregonLimits'])->name('check-limits');
    Route::post('/print-barcode/{product}', [ProductActionsController::class, 'printBarcode'])->name('print-barcode');
    Route::post('/print-exit-label/{product}', [ProductActionsController::class, 'printExitLabel'])->name('print-exit-label');
    Route::get('/metrc-info/{product}', [ProductActionsController::class, 'getMetrcDetails'])->name('metrc-info');
});

// Product Management Routes
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductsController::class, 'index'])->name('index');
    Route::get('/create', [ProductsController::class, 'create'])->name('create');
    Route::post('/', [ProductsController::class, 'store'])->name('store');
    Route::get('/{product}', [ProductsController::class, 'show'])->name('show');
    Route::get('/{product}/edit', [ProductsController::class, 'edit'])->name('edit');
    Route::patch('/{product}', [ProductsController::class, 'update'])->name('update');
    Route::delete('/{product}', [ProductsController::class, 'destroy'])->name('destroy');
    
    // Product Actions
    Route::post('/{product}/transfer-room', [ProductsController::class, 'transferRoom'])->name('transfer-room');
    Route::post('/{product}/adjust-quantity', [ProductsController::class, 'adjustQuantity'])->name('adjust-quantity');
    Route::post('/{product}/update-pricing', [ProductsController::class, 'updatePricing'])->name('update-pricing');
    Route::get('/{product}/barcode', [ProductsController::class, 'generateBarcode'])->name('barcode');
    Route::get('/{product}/label', [ProductsController::class, 'generateLabel'])->name('label');
    
    // Bulk Actions
    Route::post('/bulk-transfer', [ProductsController::class, 'bulkTransfer'])->name('bulk-transfer');
    Route::post('/bulk-pricing', [ProductsController::class, 'bulkPricing'])->name('bulk-pricing');
    Route::post('/bulk-delete', [ProductsController::class, 'bulkDelete'])->name('bulk-delete');
    
    // Import/Export
    Route::get('/export', [ProductsController::class, 'export'])->name('export');
    Route::post('/import', [ProductsController::class, 'import'])->name('import');
    
    // Search and Filter
    Route::get('/search/{query}', [ProductsController::class, 'search'])->name('search');
    Route::get('/category/{category}', [ProductsController::class, 'byCategory'])->name('by-category');
    Route::get('/room/{room}', [ProductsController::class, 'byRoom'])->name('by-room');
    Route::get('/low-stock', [ProductsController::class, 'lowStock'])->name('low-stock');
    Route::get('/out-of-stock', [ProductsController::class, 'outOfStock'])->name('out-of-stock');
    Route::get('/expiring', [ProductsController::class, 'expiring'])->name('expiring');
});

// Customer Management Routes
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomersController::class, 'index'])->name('index');
    Route::get('/create', [CustomersController::class, 'create'])->name('create');
    Route::post('/', [CustomersController::class, 'store'])->name('store');
    Route::get('/{customer}', [CustomersController::class, 'show'])->name('show');
    Route::get('/{customer}/edit', [CustomersController::class, 'edit'])->name('edit');
    Route::patch('/{customer}', [CustomersController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomersController::class, 'destroy'])->name('destroy');
    
    // Customer Actions
    Route::get('/{customer}/purchase-history', [CustomersController::class, 'purchaseHistory'])->name('purchase-history');
    Route::post('/{customer}/add-loyalty-points', [CustomersController::class, 'addLoyaltyPoints'])->name('add-loyalty-points');
    Route::post('/{customer}/redeem-points', [CustomersController::class, 'redeemPoints'])->name('redeem-points');
    Route::post('/{customer}/update-tier', [CustomersController::class, 'updateTier'])->name('update-tier');
    Route::post('/{customer}/start-sale', [CustomersController::class, 'startSale'])->name('start-sale');
    
    // Search and Export
    Route::get('/search/{query}', [CustomersController::class, 'search'])->name('search');
    Route::get('/export', [CustomersController::class, 'export'])->name('export');
    Route::get('/loyalty-members', [CustomersController::class, 'loyaltyMembers'])->name('loyalty-members');
    Route::get('/medical-patients', [CustomersController::class, 'medicalPatients'])->name('medical-patients');
    Route::get('/high-value', [CustomersController::class, 'highValue'])->name('high-value');
});

// Sales Management Routes
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('show');
    Route::get('/{sale}/receipt', [SalesController::class, 'receipt'])->name('receipt');
    Route::post('/{sale}/void', [SalesController::class, 'void'])->name('void');
    Route::post('/{sale}/refund', [SalesController::class, 'refund'])->name('refund');
    Route::post('/{sale}/reprint-receipt', [SalesController::class, 'reprintReceipt'])->name('reprint-receipt');
    
    // Sale Reports
    Route::get('/report/daily', [SalesController::class, 'dailyReport'])->name('daily-report');
    Route::get('/report/weekly', [SalesController::class, 'weeklyReport'])->name('weekly-report');
    Route::get('/report/monthly', [SalesController::class, 'monthlyReport'])->name('monthly-report');
    Route::get('/report/custom', [SalesController::class, 'customReport'])->name('custom-report');
    
    // Export
    Route::get('/export', [SalesController::class, 'export'])->name('export');
    Route::get('/export/metrc', [SalesController::class, 'exportMetrc'])->name('export-metrc');
});

// Analytics and Reporting Routes
Route::prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
    
    // Sales Analytics
    Route::get('/sales/overview', [AnalyticsController::class, 'salesOverview'])->name('sales-overview');
    Route::get('/sales/trends', [AnalyticsController::class, 'salesTrends'])->name('sales-trends');
    Route::get('/sales/by-category', [AnalyticsController::class, 'salesByCategory'])->name('sales-by-category');
    Route::get('/sales/by-employee', [AnalyticsController::class, 'salesByEmployee'])->name('sales-by-employee');
    Route::get('/sales/by-time', [AnalyticsController::class, 'salesByTime'])->name('sales-by-time');
    
    // Product Analytics
    Route::get('/products/performance', [AnalyticsController::class, 'productPerformance'])->name('product-performance');
    Route::get('/products/top-selling', [AnalyticsController::class, 'topSellingProducts'])->name('top-selling');
    Route::get('/products/slow-moving', [AnalyticsController::class, 'slowMovingProducts'])->name('slow-moving');
    Route::get('/products/margin-analysis', [AnalyticsController::class, 'marginAnalysis'])->name('margin-analysis');
    
    // Customer Analytics
    Route::get('/customers/overview', [AnalyticsController::class, 'customerOverview'])->name('customer-overview');
    Route::get('/customers/retention', [AnalyticsController::class, 'customerRetention'])->name('customer-retention');
    Route::get('/customers/lifetime-value', [AnalyticsController::class, 'customerLifetimeValue'])->name('customer-lifetime-value');
    Route::get('/customers/loyalty-program', [AnalyticsController::class, 'loyaltyProgramAnalytics'])->name('loyalty-program');
    
    // Inventory Analytics
    Route::get('/inventory/turnover', [AnalyticsController::class, 'inventoryTurnover'])->name('inventory-turnover');
    Route::get('/inventory/valuation', [AnalyticsController::class, 'inventoryValuation'])->name('inventory-valuation');
    Route::get('/inventory/forecasting', [AnalyticsController::class, 'inventoryForecasting'])->name('inventory-forecasting');
});

// Employee Management Routes
Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/', [EmployeesController::class, 'index'])->name('index');
    Route::get('/create', [EmployeesController::class, 'create'])->name('create');
    Route::post('/', [EmployeesController::class, 'store'])->name('store');
    Route::get('/{employee}', [EmployeesController::class, 'show'])->name('show');
    Route::get('/{employee}/edit', [EmployeesController::class, 'edit'])->name('edit');
    Route::patch('/{employee}', [EmployeesController::class, 'update'])->name('update');
    Route::delete('/{employee}', [EmployeesController::class, 'destroy'])->name('destroy');
    
    // Employee Performance
    Route::get('/{employee}/performance', [EmployeesController::class, 'performance'])->name('performance');
    Route::get('/{employee}/sales-history', [EmployeesController::class, 'salesHistory'])->name('sales-history');
    Route::post('/{employee}/update-permissions', [EmployeesController::class, 'updatePermissions'])->name('update-permissions');
});

// Room and Drawer Management Routes
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', [RoomsController::class, 'index'])->name('index');
    Route::post('/', [RoomsController::class, 'store'])->name('store');
    Route::get('/{room}', [RoomsController::class, 'show'])->name('show');
    Route::patch('/{room}', [RoomsController::class, 'update'])->name('update');
    Route::delete('/{room}', [RoomsController::class, 'destroy'])->name('destroy');
    
    // Room Actions
    Route::get('/{room}/products', [RoomsController::class, 'products'])->name('products');
    Route::post('/{room}/transfer-all', [RoomsController::class, 'transferAll'])->name('transfer-all');
    Route::get('/{room}/audit', [RoomsController::class, 'audit'])->name('audit');
});

// Deals and Promotions Routes
Route::prefix('deals')->name('deals.')->group(function () {
    Route::get('/', [DealsController::class, 'index'])->name('index');
    Route::get('/create', [DealsController::class, 'create'])->name('create');
    Route::post('/', [DealsController::class, 'store'])->name('store');
    Route::get('/{deal}', [DealsController::class, 'show'])->name('show');
    Route::get('/{deal}/edit', [DealsController::class, 'edit'])->name('edit');
    Route::patch('/{deal}', [DealsController::class, 'update'])->name('update');
    Route::delete('/{deal}', [DealsController::class, 'destroy'])->name('destroy');
    
    // Deal Actions
    Route::post('/{deal}/activate', [DealsController::class, 'activate'])->name('activate');
    Route::post('/{deal}/deactivate', [DealsController::class, 'deactivate'])->name('deactivate');
    Route::get('/{deal}/performance', [DealsController::class, 'performance'])->name('performance');
});

// Loyalty Program Routes
Route::prefix('loyalty')->name('loyalty.')->group(function () {
    Route::get('/', [LoyaltyController::class, 'index'])->name('index');
    Route::get('/settings', [LoyaltyController::class, 'settings'])->name('settings');
    Route::post('/settings', [LoyaltyController::class, 'updateSettings'])->name('update-settings');
    Route::get('/transactions', [LoyaltyController::class, 'transactions'])->name('transactions');
    Route::get('/tiers', [LoyaltyController::class, 'tiers'])->name('tiers');
    Route::post('/tiers', [LoyaltyController::class, 'updateTiers'])->name('update-tiers');
    
    // Manual Point Management
    Route::post('/add-points', [LoyaltyController::class, 'addPoints'])->name('add-points');
    Route::post('/redeem-points', [LoyaltyController::class, 'redeemPoints'])->name('redeem-points');
    Route::post('/adjust-points', [LoyaltyController::class, 'adjustPoints'])->name('adjust-points');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    
    // Financial Reports
    Route::get('/financial/daily-sales', [ReportsController::class, 'dailySales'])->name('daily-sales');
    Route::get('/financial/weekly-sales', [ReportsController::class, 'weeklySales'])->name('weekly-sales');
    Route::get('/financial/monthly-sales', [ReportsController::class, 'monthlySales'])->name('monthly-sales');
    Route::get('/financial/tax-report', [ReportsController::class, 'taxReport'])->name('tax-report');
    Route::get('/financial/payment-methods', [ReportsController::class, 'paymentMethods'])->name('payment-methods');
    
    // Inventory Reports
    Route::get('/inventory/current-stock', [ReportsController::class, 'currentStock'])->name('current-stock');
    Route::get('/inventory/low-stock', [ReportsController::class, 'lowStock'])->name('low-stock');
    Route::get('/inventory/expiring-products', [ReportsController::class, 'expiringProducts'])->name('expiring-products');
    Route::get('/inventory/movement', [ReportsController::class, 'inventoryMovement'])->name('inventory-movement');
    Route::get('/inventory/valuation', [ReportsController::class, 'inventoryValuation'])->name('inventory-valuation');
    
    // Compliance Reports
    Route::get('/compliance/metrc-sync', [ReportsController::class, 'metrcSync'])->name('metrc-sync');
    Route::get('/compliance/audit-trail', [ReportsController::class, 'auditTrail'])->name('audit-trail');
    Route::get('/compliance/oregon-limits', [ReportsController::class, 'oregonLimits'])->name('oregon-limits');
    
    // Customer Reports
    Route::get('/customers/overview', [ReportsController::class, 'customerOverview'])->name('customer-overview');
    Route::get('/customers/loyalty', [ReportsController::class, 'loyaltyReport'])->name('loyalty-report');
    Route::get('/customers/medical-patients', [ReportsController::class, 'medicalPatients'])->name('medical-patients');
});

// Settings Routes
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::post('/', [SettingsController::class, 'update'])->name('update');
    
    // Tax Settings
    Route::get('/tax', [SettingsController::class, 'tax'])->name('tax');
    Route::post('/tax', [SettingsController::class, 'updateTax'])->name('update-tax');
    
    // POS Settings
    Route::get('/pos', [SettingsController::class, 'pos'])->name('pos');
    Route::post('/pos', [SettingsController::class, 'updatePos'])->name('update-pos');
    
    // Printer Settings
    Route::get('/printers', [SettingsController::class, 'printers'])->name('printers');
    Route::post('/printers', [SettingsController::class, 'updatePrinters'])->name('update-printers');
    Route::post('/printers/test', [SettingsController::class, 'testPrinter'])->name('test-printer');
    
    // METRC Integration
    Route::get('/metrc', [SettingsController::class, 'metrc'])->name('metrc');
    Route::post('/metrc', [SettingsController::class, 'updateMetrc'])->name('update-metrc');
    Route::post('/metrc/test', [SettingsController::class, 'testMetrc'])->name('test-metrc');
    
    // Backup and Restore
    Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
    Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('create-backup');
    Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('restore-backup');
});

// Payment Processing Routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/process', [PaymentController::class, 'process'])->name('process');
    Route::post('/void/{transaction}', [PaymentController::class, 'void'])->name('void');
    Route::post('/refund/{transaction}', [PaymentController::class, 'refund'])->name('refund');
    Route::get('/batch-report', [PaymentController::class, 'batchReport'])->name('batch-report');
    Route::post('/close-batch', [PaymentController::class, 'closeBatch'])->name('close-batch');
});

// Order Queue Routes (for online orders, if applicable)
Route::prefix('order-queue')->name('order-queue.')->group(function () {
    Route::get('/', [OrderQueueController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderQueueController::class, 'show'])->name('show');
    Route::post('/{order}/fulfill', [OrderQueueController::class, 'fulfill'])->name('fulfill');
    Route::post('/{order}/cancel', [OrderQueueController::class, 'cancel'])->name('cancel');
    Route::post('/{order}/partial-fulfill', [OrderQueueController::class, 'partialFulfill'])->name('partial-fulfill');
});

// Price Tiers Routes
Route::prefix('price-tiers')->name('price-tiers.')->group(function () {
    Route::get('/', [PriceTiersController::class, 'index'])->name('index');
    Route::post('/', [PriceTiersController::class, 'store'])->name('store');
    Route::patch('/{priceTier}', [PriceTiersController::class, 'update'])->name('update');
    Route::delete('/{priceTier}', [PriceTiersController::class, 'destroy'])->name('destroy');
    Route::post('/apply-to-products', [PriceTiersController::class, 'applyToProducts'])->name('apply-to-products');
});

// API Routes for AJAX requests
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/products/search', [ProductsController::class, 'apiSearch'])->name('products.search');
    Route::get('/customers/search', [CustomersController::class, 'apiSearch'])->name('customers.search');
    Route::get('/sales/recent', [SalesController::class, 'recentSales'])->name('sales.recent');
    Route::get('/analytics/quick-stats', [AnalyticsController::class, 'quickStats'])->name('analytics.quick-stats');
    Route::post('/cart/validate', [POSController::class, 'validateCart'])->name('cart.validate');
    Route::get('/metrc/product/{tag}', [POSController::class, 'getMetrcProduct'])->name('metrc.product');
});

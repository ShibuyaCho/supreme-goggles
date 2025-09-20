<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $filterStatus = $request->get('status', 'all');
        $filterPayment = $request->get('payment_method', 'all');
        $filterEmployee = $request->get('employee', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $selectedTab = $request->get('tab', 'sales');

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        $useSupabase = !empty($supabaseUrl) && !empty($supabaseKey);

        if ($useSupabase) {
            // Fetch from Supabase REST and shape data for Blade
            $params = [
                'select' => '*',
                'order' => 'created_at.desc',
                'limit' => 1000,
            ];
            $rows = [];
            try {
                $resp = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/sales', $params);
                if ($resp->successful()) $rows = $resp->json() ?: [];
            } catch (\Throwable $e) {
                $rows = [];
            }

            // Apply filters client-side
            $rows = collect($rows);
            if ($searchQuery) {
                $q = mb_strtolower($searchQuery);
                $rows = $rows->filter(function($r) use ($q) {
                    $sn = mb_strtolower((string)($r['sale_number'] ?? $r['id'] ?? ''));
                    $cn = mb_strtolower((string)($r['customer']['full_name'] ?? $r['customer']['name'] ?? ''));
                    return str_contains($sn, $q) || ($cn && str_contains($cn, $q));
                });
            }
            if ($filterStatus !== 'all') {
                $rows = $rows->where('status', $filterStatus);
            }
            if ($filterPayment !== 'all') {
                $rows = $rows->where('payment_method', $filterPayment);
            }
            if ($filterEmployee !== 'all') {
                $rows = $rows->where('employee_id', $filterEmployee);
            }
            if ($dateFrom || $dateTo) {
                $rows = $rows->filter(function($r) use ($dateFrom, $dateTo) {
                    try {
                        $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
                        $created = \Carbon\Carbon::parse($r['created_at'] ?? null)->setTimezone($tz);
                        $from = $dateFrom ? \Carbon\Carbon::parse($dateFrom, $tz)->startOfDay() : null;
                        $to = $dateTo ? \Carbon\Carbon::parse($dateTo, $tz)->endOfDay() : null;
                    } catch (\Throwable $e) {
                        return true;
                    }
                    if ($from && $created->lt($from)) return false;
                    if ($to && $created->gt($to)) return false;
                    return true;
                });
            }

            // Sorting
            $rows = $rows->sortBy([
                [$sortBy, strtolower($sortOrder) === 'desc' ? 'desc' : 'asc'],
            ]);

            // Map to objects expected by Blade
            $mapped = $rows->map(function($r){
                $cart = is_array($r['cart'] ?? null) ? $r['cart'] : [];
                $itemCount = collect($cart)->sum(function($i){ return (int)($i['quantity'] ?? 1); });
                $firstName = $cart && isset($cart[0]['name']) ? $cart[0]['name'] : null;
                $customer = $r['customer'] ?? null;
                $customerObj = is_array($customer) ? (object) [
                    'full_name' => $customer['full_name'] ?? ($customer['name'] ?? 'Walk-in Customer'),
                    'type' => $customer['type'] ?? ($customer['customer_type'] ?? 'recreational'),
                ] : null;
                $employeeObj = (object) [ 'full_name' => 'Unknown' ];
                $saleItems = collect($cart)->map(function($i){
                    return (object) [
                        'product' => (object) ['name' => $i['name'] ?? 'Product'],
                        'quantity' => (int)($i['quantity'] ?? 1),
                        'unit_price' => (float)($i['price'] ?? 0),
                        'total_price' => (float)((($i['price'] ?? 0) * ($i['quantity'] ?? 1))),
                    ];
                });
                return (object) [
                    'id' => $r['id'] ?? null,
                    'sale_number' => $r['sale_number'] ?? ('S-' . ($r['id'] ?? '')),
                    'created_at' => Carbon::parse($r['created_at'] ?? Carbon::now()),
                    'customer' => $customerObj,
                    'employee' => $employeeObj,
                    'till_number' => $r['till_number'] ?? null,
                    'item_count' => $itemCount,
                    'saleItems' => $saleItems,
                    'subtotal' => (float)($r['subtotal'] ?? 0),
                    'tax_amount' => (float)($r['tax'] ?? 0),
                    'discount_amount' => (float)($r['discount_amount'] ?? 0),
                    'total_amount' => (float)($r['total'] ?? 0),
                    'payment_method' => $r['payment_method'] ?? 'cash',
                    'payment_reference' => $r['payment_reference'] ?? ($r['card_last_four'] ?? null),
                    'status' => $r['status'] ?? 'completed',
                ];
            })->values();

            // De-duplicate by sale_number to avoid duplicates
            $mapped = $mapped->unique(function($r){ return $r->sale_number ?? ($r->id ?? null); })->values();

            // Pagination
            $page = max(1, (int)$request->get('page', 1));
            $perPage = 20;
            $total = $mapped->count();
            $items = $mapped->slice(($page-1)*$perPage, $perPage)->values();
            $sales = new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => url()->current(),
                'query' => $request->query(),
            ]);

            // If Supabase returned data, render it; otherwise fall back to Eloquent below
            if ($total > 0) {
                // Filter options
                $employees = collect([]); // Unknown without relational DB
                $paymentMethods = $mapped->pluck('payment_method')->unique();

                // Analytics
                $analytics = $this->getSalesAnalyticsFromArray($mapped, $request);

                return view('sales.index', compact(
                    'sales',
                    'searchQuery',
                    'filterStatus',
                    'filterPayment',
                    'filterEmployee',
                    'dateFrom',
                    'dateTo',
                    'sortBy',
                    'sortOrder',
                    'selectedTab',
                    'employees',
                    'paymentMethods',
                    'analytics'
                ));
            }
        }

        // Default: Eloquent (MySQL)
        $query = Sale::with(['customer', 'employee', 'saleItems.product']);
        try { if (\Illuminate\Support\Facades\Schema::hasColumn('sales','store_id')) { $query->where('store_id', \App\Helpers\StoreContext::id()); } } catch (\Throwable $e) {}

        // Apply search filter
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('sale_number', 'like', "%{$searchQuery}%")
                  ->orWhereHas('customer', function($customerQuery) use ($searchQuery) {
                      $customerQuery->where('first_name', 'like', "%{$searchQuery}%")
                                   ->orWhere('last_name', 'like', "%{$searchQuery}%")
                                   ->orWhere('email', 'like', "%{$searchQuery}%");
                  })
                  ->orWhereHas('employee', function($employeeQuery) use ($searchQuery) {
                      $employeeQuery->where('first_name', 'like', "%{$searchQuery}%")
                                   ->orWhere('last_name', 'like', "%{$searchQuery}%");
                  });
            });
        }

        // Apply status filter
        if ($filterStatus !== 'all') {
            $query->where('status', $filterStatus);
        }

        // Apply payment method filter
        if ($filterPayment !== 'all') {
            $query->where('payment_method', $filterPayment);
        }

        // Apply employee filter
        if ($filterEmployee !== 'all') {
            $query->where('employee_id', $filterEmployee);
        }

        // Apply date filters (convert local day to UTC boundaries)
        $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
        if ($dateFrom) {
            $startUtc = Carbon::parse($dateFrom, $tz)->startOfDay()->setTimezone('UTC');
            $query->where('created_at', '>=', $startUtc);
        }
        if ($dateTo) {
            $endUtc = Carbon::parse($dateTo, $tz)->endOfDay()->setTimezone('UTC');
            $query->where('created_at', '<=', $endUtc);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Keep only latest row per sale_number (best-effort) to prevent duplicates
        try {
            $query->whereIn('id', function($q){
                $q->select(DB::raw('MAX(id)'))
                  ->from('sales')
                  ->groupBy('sale_number');
            });
        } catch (\Throwable $e) { /* ignore */ }

        $sales = $query->paginate(20);

        // Get filter options
        $employees = Employee::all();
        $paymentMethods = Sale::select('payment_method')->distinct()->pluck('payment_method');

        // Get analytics data
        $analytics = $this->getSalesAnalytics($request);

        return view('sales.index', compact(
            'sales',
            'searchQuery',
            'filterStatus',
            'filterPayment',
            'filterEmployee',
            'dateFrom',
            'dateTo',
            'sortBy',
            'sortOrder',
            'selectedTab',
            'employees',
            'paymentMethods',
            'analytics'
        ));
    }
    
    public function show($id)
    {
        $sale = Sale::with([
            'customer',
            'employee',
            'saleItems.product',
            'voidedByEmployee'
        ])->findOrFail($id);

        // Get related transactions (refunds, voids)
        $relatedTransactions = Sale::where('id', '!=', $id)
            ->where(function($query) use ($sale) {
                $query->where('sale_number', 'like', $sale->sale_number . '%')
                      ->orWhere('notes', 'like', '%' . $sale->sale_number . '%');
            })
            ->get();

        return view('sales.show', compact('sale', 'relatedTransactions'));
    }

    // API JSON: single sale with relations
    public function apiShow($id)
    {
        $sale = Sale::with([
            'customer',
            'employee',
            'saleItems.product',
            'voidedByEmployee'
        ])->findOrFail($id);
        return response()->json($sale);
    }
    
    public function receipt($id)
    {
        $sale = Sale::with([
            'customer',
            'employee',
            'saleItems.product'
        ])->findOrFail($id);
        
        // Generate receipt PDF (fallback to HTML if PDF library unavailable)
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return view('sales.receipt', compact('sale'));
        }

        $pdf = Pdf::loadView('sales.receipt', compact('sale'));

        // Mark as printed
        $sale->markAsPrinted();

        return $pdf->download("receipt_{$sale->sale_number}.pdf");
    }
    
    public function void(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        if (!$sale->canBeVoided()) {
            return response()->json([
                'error' => 'This sale cannot be voided'
            ], 400);
        }
        
        $request->validate([
            'reason' => 'required|string|max:500',
            'employee_pin' => 'required|string'
        ]);
        
        // Verify employee PIN (implement PIN verification)
        if (!$this->verifyEmployeePin($request->employee_pin)) {
            return response()->json([
                'error' => 'Invalid employee PIN'
            ], 401);
        }
        
        try {
            DB::beginTransaction();
            
            // Void the sale
            $sale->voidSale($request->reason, Auth::id());
            
            DB::commit();
            
            return response()->json([
                'message' => 'Sale voided successfully',
                'sale' => $sale->refresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Error voiding sale: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function refund(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        if ($sale->status !== 'completed') {
            return response()->json([
                'error' => 'Only completed sales can be refunded'
            ], 400);
        }
        
        $request->validate([
            'refund_type' => 'required|in:full,partial',
            'refund_amount' => 'required_if:refund_type,partial|numeric|min:0',
            'items' => 'required_if:refund_type,partial|array',
            'reason' => 'required|string|max:500',
            'employee_pin' => 'required|string'
        ]);
        
        // Verify employee PIN
        if (!$this->verifyEmployeePin($request->employee_pin)) {
            return response()->json([
                'error' => 'Invalid employee PIN'
            ], 401);
        }
        
        try {
            DB::beginTransaction();
            
            // Create refund sale
            $refundAmount = $request->refund_type === 'full' 
                ? $sale->total_amount 
                : $request->refund_amount;
            
            $refundSale = Sale::create([
                'sale_number' => $this->generateRefundSaleNumber($sale->sale_number),
                'customer_id' => $sale->customer_id,
                'employee_id' => Auth::id(),
                'customer_type' => $sale->customer_type,
                'customer_info' => $sale->customer_info,
                'subtotal' => -$refundAmount,
                'tax_amount' => -($sale->tax_amount * ($refundAmount / $sale->total_amount)),
                'total_amount' => -$refundAmount,
                'payment_method' => $sale->payment_method,
                'status' => 'completed',
                'notes' => 'Refund for sale ' . $sale->sale_number . ': ' . $request->reason
            ]);
            
            // Handle partial refunds
            if ($request->refund_type === 'partial' && $request->items) {
                foreach ($request->items as $itemData) {
                    $originalItem = SaleItem::find($itemData['id']);
                    if ($originalItem && $originalItem->sale_id === $sale->id) {
                        SaleItem::create([
                            'sale_id' => $refundSale->id,
                            'product_id' => $originalItem->product_id,
                            'product_name' => $originalItem->product_name,
                            'product_category' => $originalItem->product_category,
                            'quantity' => -$itemData['quantity'],
                            'unit_price' => $originalItem->unit_price,
                            'total_price' => -($originalItem->unit_price * $itemData['quantity'])
                        ]);
                        
                        // Restore inventory
                        if ($originalItem->product) {
                            $originalItem->product->increment('quantity', $itemData['quantity']);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Refund processed successfully',
                'refund_sale' => $refundSale
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Error processing refund: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function reprintReceipt($id)
    {
        $sale = Sale::with([
            'customer',
            'employee',
            'saleItems.product'
        ])->findOrFail($id);

        // Render Blade HTML (reader-friendly); PDF if package present
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->view('sales.receipt', ['sale' => $sale, 'reprint' => true]);
        }
        $pdf = Pdf::loadView('sales.receipt', ['sale' => $sale, 'reprint' => true]);
        return $pdf->download("receipt_{$sale->sale_number}_reprint.pdf");
    }

    public function reprintExitLabels($id)
    {
        $sale = Sale::with(['saleItems.product'])->findOrFail($id);
        return response()->view('sales.exit-labels', ['sale' => $sale]);
    }
    
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
        $startUtc = Carbon::parse($date, $tz)->startOfDay()->setTimezone('UTC');
        $endUtc = Carbon::parse($date, $tz)->endOfDay()->setTimezone('UTC');

        $sales = Sale::with(['employee', 'saleItems'])
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->where('status', 'completed')
            ->get();
        
        $report = $this->generateDailyReportData($sales, $date);
        
        if ($request->get('format') === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $html = view('sales.reports.daily', compact('report', 'date'))->render();
                $filename = "daily_sales_report_{$date}.html";
                return response($html, 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                    ->header('X-Export-Fallback', 'pdf->html')
                    ->header('X-Export-Filename', $filename);
            }
            $pdf = Pdf::loadView('sales.reports.daily', compact('report', 'date'));
            return $pdf->download("daily_sales_report_{$date}.pdf");
        }
        
        return view('sales.reports.daily', compact('report', 'date'));
    }
    
    public function weeklyReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        
        $sales = Sale::with(['employee', 'saleItems'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();
        
        $report = $this->generateWeeklyReportData($sales, $startDate, $endDate);
        
        if ($request->get('format') === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $html = view('sales.reports.weekly', compact('report', 'startDate', 'endDate'))->render();
                $filename = "weekly_sales_report_{$startDate}_to_{$endDate}.html";
                return response($html, 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                    ->header('X-Export-Fallback', 'pdf->html')
                    ->header('X-Export-Filename', $filename);
            }
            $pdf = Pdf::loadView('sales.reports.weekly', compact('report', 'startDate', 'endDate'));
            return $pdf->download("weekly_sales_report_{$startDate}_to_{$endDate}.pdf");
        }
        
        return view('sales.reports.weekly', compact('report', 'startDate', 'endDate'));
    }
    
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();
        
        $sales = Sale::with(['employee', 'saleItems', 'customer'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();
        
        $report = $this->generateMonthlyReportData($sales, $month);
        
        if ($request->get('format') === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $html = view('sales.reports.monthly', compact('report', 'month'))->render();
                $filename = "monthly_sales_report_{$month}.html";
                return response($html, 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                    ->header('X-Export-Fallback', 'pdf->html')
                    ->header('X-Export-Filename', $filename);
            }
            $pdf = Pdf::loadView('sales.reports.monthly', compact('report', 'month'));
            return $pdf->download("monthly_sales_report_{$month}.pdf");
        }
        
        return view('sales.reports.monthly', compact('report', 'month'));
    }
    
    public function export(Request $request)
    {
        // Apply same filters as index
        $query = Sale::with(['customer', 'employee']);

        // ... apply filters similar to index method

        $sales = $query->get();

        $filename = 'sales_export_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Sale Number', 'Date', 'Customer', 'Employee', 'Items', 'Subtotal',
                'Tax', 'Total', 'Payment Method', 'Status'
            ]);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->sale_number,
                    $sale->created_at->format('Y-m-d H:i:s'),
                    $sale->customer ? $sale->customer->full_name : 'Walk-in',
                    $sale->employee ? $sale->employee->full_name : 'Unknown',
                    $sale->item_count,
                    $sale->subtotal,
                    $sale->tax_amount,
                    $sale->total_amount,
                    $sale->payment_method,
                    $sale->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // TEMP: quick diagnostics endpoint to verify data exists
    public function diagCount()
    {
        return response()->json([
            'count' => \App\Models\Sale::count(),
            'completed' => \App\Models\Sale::where('status','completed')->count(),
            'last' => \App\Models\Sale::with(['saleItems'])->orderBy('id','desc')->first(),
        ]);
    }

    // TEMP: create a fake sale for diagnostics
    public function diagCreate()
    {
        $employeeId = \App\Models\Employee::query()->value('id') ?? \App\Models\Employee::create([
            'employee_id' => 'POS-' . now()->format('YmdHis'),
            'first_name' => 'POS',
            'last_name' => 'User',
            'email' => 'pos@example.com',
            'pin' => bcrypt('0000'),
            'password' => bcrypt(str()->random(12)),
            'role' => 'cashier',
            'is_active' => true,
        ])->id;

        $sale = \App\Models\Sale::create([
            'sale_number' => \App\Models\Sale::generateSaleNumber(),
            'customer_id' => null,
            'employee_id' => $employeeId,
            'customer_type' => 'recreational',
            'customer_info' => null,
            'subtotal' => 10.00,
            'tax_amount' => 2.00,
            'discount_amount' => 0,
            'total_amount' => 12.00,
            'payment_method' => 'cash',
            'payment_reference' => null,
            'status' => 'completed',
            'cart_items' => [ ['id'=>1,'name'=>'Test Item','price'=>10,'quantity'=>1] ],
            'applied_deals' => [],
            'tax_rate' => 0.20,
            'notes' => 'diagCreate',
        ]);
        \App\Models\SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => null,
            'product_name' => 'Test Item',
            'product_category' => 'Diagnostics',
            'quantity' => 1,
            'unit_price' => 10.00,
            'total_price' => 10.00,
            'discount_amount' => 0,
        ]);
        return response()->json(['ok'=>true,'sale'=>$sale->load('saleItems')]);
    }

    // JSON: Recent/filtered sales for SPA "Sales Transactions"
    public function recentSales(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $status = $request->get('status', 'completed');
        $payment = $request->get('payment_method', null);
        $employee = $request->get('employee', null);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $limit = (int) $request->get('limit', 200);

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_ANON_KEY');
        $useSupabase = !empty($supabaseUrl) && !empty($supabaseKey);

        $merged = collect();

        if ($useSupabase) {
            // Fetch from Supabase REST and apply filters in PHP
            $params = [
                'select' => '*',
                'order' => 'created_at.desc',
                'limit' => max(1, min(1000, $limit)),
            ];
            if ($request->filled('start_at') || $request->filled('end_at') || $dateFrom || $dateTo) {
                $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
                $fromUtc = $request->filled('start_at') ? Carbon::parse($request->get('start_at'))->utc()->toISOString() : ($dateFrom ? Carbon::parse($dateFrom, $tz)->startOfDay()->utc()->toISOString() : null);
                $toUtc = $request->filled('end_at') ? Carbon::parse($request->get('end_at'))->utc()->toISOString() : ($dateTo ? Carbon::parse($dateTo, $tz)->endOfDay()->utc()->toISOString() : null);
                if ($fromUtc && $toUtc) {
                    $params['and'] = '(created_at.gte.' . $fromUtc . ',created_at.lte.' . $toUtc . ')';
                } elseif ($fromUtc) {
                    $params['created_at'] = 'gte.' . $fromUtc;
                } elseif ($toUtc) {
                    $params['created_at'] = 'lte.' . $toUtc;
                }
            }
            $rows = [];
            try {
                $resp = Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/sales', $params);
                if ($resp->successful()) $rows = $resp->json() ?: [];
            } catch (\Throwable $e) { $rows = []; }

            $rows = collect($rows);
            if ($searchQuery) {
                $q = mb_strtolower($searchQuery);
                $rows = $rows->filter(function($r) use ($q) {
                    $sn = mb_strtolower((string)($r['sale_number'] ?? $r['id'] ?? ''));
                    $cn = mb_strtolower((string)($r['customer']['full_name'] ?? $r['customer']['name'] ?? ''));
                    return str_contains($sn, $q) || ($cn && str_contains($cn, $q));
                });
            }
            if ($status && $status !== 'all') {
                $rows = $rows->filter(function($r) use ($status){
                    $v = strtolower((string)($r['status'] ?? ''));
                    return $v === strtolower($status);
                });
            }
            if ($payment && $payment !== 'all') {
                $rows = $rows->where('payment_method', $payment);
            }
            if ($employee && $employee !== 'all') {
                $rows = $rows->where('employee_id', $employee);
            }
            if ($request->filled('start_at') || $request->filled('end_at') || $dateFrom || $dateTo) {
                $rows = $rows->filter(function($r) use ($request, $dateFrom, $dateTo) {
                    try {
                        $startAt = $request->get('start_at');
                        $endAt = $request->get('end_at');
                        if ($startAt || $endAt) {
                            $created = \Carbon\Carbon::parse($r['created_at'] ?? null);
                            $from = $startAt ? \Carbon\Carbon::parse($startAt) : null;
                            $to = $endAt ? \Carbon\Carbon::parse($endAt) : null;
                            if ($from && $created->lt($from)) return false;
                            if ($to && $created->gt($to)) return false;
                            return true;
                        }
                        $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
                        $created = \Carbon\Carbon::parse($r['created_at'] ?? null)->setTimezone($tz);
                        $from = $dateFrom ? \Carbon\Carbon::parse($dateFrom, $tz)->startOfDay() : null;
                        $to = $dateTo ? \Carbon\Carbon::parse($dateTo, $tz)->endOfDay() : null;
                        if ($from && $created->lt($from)) return false;
                        if ($to && $created->gt($to)) return false;
                        return true;
                    } catch (\Throwable $e) { return true; }
                });
            }

            // Build employee name map from Supabase employees table (by employee_id)
            $empMap = collect();
            try {
                $empIds = $rows->pluck('employee_id')->filter()->unique()->values();
                if ($empIds->count() > 0) {
                    $in = 'in.(' . $empIds->map(fn($v) => '"' . str_replace('"','\"', (string)$v) . '"')->implode(',') . ')';
                    $er = \Illuminate\Support\Facades\Http::withHeaders([
                        'apikey' => $supabaseKey,
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'Accept' => 'application/json',
                    ])->get(rtrim($supabaseUrl,'/') . '/rest/v1/employees', [ 'select' => 'employee_id,first_name,last_name', 'employee_id' => $in ]);
                    if ($er->successful()) {
                        $elist = collect($er->json() ?: []);
                        $empMap = $elist->mapWithKeys(function($e){
                            $name = trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? ''));
                            return [ (string)($e['employee_id'] ?? '') => $name ?: 'Unknown' ];
                        });
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }

            // Normalize Supabase rows
            $normalized = $rows->map(function($r) use ($empMap){
                $cart = is_array($r['cart'] ?? null) ? $r['cart'] : [];
                $itemCount = collect($cart)->sum(function($i){ return (int)($i['quantity'] ?? 1); });
                $saleItems = collect($cart)->map(function($i){
                    return [
                        'product_id' => null,
                        'product_name' => $i['name'] ?? 'Product',
                        'quantity' => (int)($i['quantity'] ?? 1),
                        'unit_price' => (float)($i['price'] ?? 0),
                        'total_price' => (float)((($i['price'] ?? 0) * ($i['quantity'] ?? 1))),
                    ];
                })->values()->all();
                $empName = $empMap->get((string)($r['employee_id'] ?? ''));
                return [
                    'id' => $r['id'] ?? null,
                    'sale_number' => $r['sale_number'] ?? ('S-' . ($r['id'] ?? '')),
                    'created_at' => $r['created_at'] ?? now()->toIso8601String(),
                    'customer' => $r['customer'] ?? null,
                    'customer_type' => $r['customer_type'] ?? null,
                    'employee' => $empName ? [ 'name' => $empName ] : null,
                    'employee_name' => $empName,
                    'item_count' => $itemCount,
                    'sale_items' => $saleItems,
                    'subtotal' => (float)($r['subtotal'] ?? 0),
                    'tax_amount' => (float)($r['tax'] ?? 0),
                    'discount_amount' => (float)($r['discount_amount'] ?? 0),
                    'total_amount' => (float)($r['total'] ?? 0),
                    'payment_method' => $r['payment_method'] ?? 'cash',
                    'payment_reference' => $r['payment_reference'] ?? ($r['card_last_four'] ?? null),
                    'status' => $r['status'] ?? 'completed',
                    'source' => 'supabase',
                ];
            })->values();

            $merged = $normalized->unique(function($r){ return $r['sale_number'] ?? ($r['id'] ?? null); })->values();
        }

        // Always include local DB as a safety net, merging by sale_number/id to avoid duplicates
        $query = Sale::with(['customer', 'employee', 'saleItems.product']);
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('sale_number', 'like', "%{$searchQuery}%")
                  ->orWhereHas('customer', function($cq) use ($searchQuery) {
                      $cq->where('first_name', 'like', "%{$searchQuery}%")
                         ->orWhere('last_name', 'like', "%{$searchQuery}%")
                         ->orWhere('email', 'like', "%{$searchQuery}%");
                  })
                  ->orWhereHas('employee', function($eq) use ($searchQuery) {
                      $eq->where('first_name', 'like', "%{$searchQuery}%")
                         ->orWhere('last_name', 'like', "%{$searchQuery}%");
                  });
            });
        }
        if ($status && $status !== 'all') {
            try {
                $query->whereRaw('LOWER(status) = ?', [ strtolower($status) ]);
            } catch (\Throwable $e) {
                $query->where('status', $status);
            }
        }
        if ($payment && $payment !== 'all') {
            $query->where('payment_method', $payment);
        }
        if ($employee && $employee !== 'all') {
            $query->where('employee_id', $employee);
        }
        // Apply date filters (prefer explicit UTC window when provided)
        $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
        $startAt = request()->get('start_at');
        $endAt = request()->get('end_at');
        if ($startAt) {
            try { $query->where('created_at', '>=', Carbon::parse($startAt)->setTimezone('UTC')); } catch (\Throwable $e) {}
        } elseif ($dateFrom) {
            $startUtc = Carbon::parse($dateFrom, $tz)->startOfDay()->setTimezone('UTC');
            $query->where('created_at', '>=', $startUtc);
        }
        if ($endAt) {
            try { $query->where('created_at', '<=', Carbon::parse($endAt)->setTimezone('UTC')); } catch (\Throwable $e) {}
        } elseif ($dateTo) {
            $endUtc = Carbon::parse($dateTo, $tz)->endOfDay()->setTimezone('UTC');
            $query->where('created_at', '<=', $endUtc);
        }
        $query->orderBy('created_at', 'desc');
        $localRows = $query->limit(max(1, min(1000, $limit)))->get();

        $existingKeys = $merged->map(function($r){ return (string)($r['sale_number'] ?? $r['id'] ?? ''); })->filter()->values()->all();
        $localMapped = collect($localRows)->map(function($s){
            $itemCount = $s->saleItems->sum('quantity');
            $saleItems = $s->saleItems->map(function($i){
                return [
                    'product_id' => $i->product_id,
                    'product_name' => $i->product->name ?? $i->product_name,
                    'quantity' => (int)$i->quantity,
                    'unit_price' => (float)($i->unit_price ?? 0),
                    'total_price' => (float)($i->total_price ?? 0),
                ];
            })->values()->all();
            $empName = $s->employee ? ($s->employee->full_name ?? (($s->employee->first_name ?? '') . ' ' . ($s->employee->last_name ?? ''))) : null;
            $customer = $s->customer ? [ 'full_name' => $s->customer->full_name ] : null;
            return [
                'id' => $s->id,
                'sale_number' => $s->sale_number,
                'created_at' => $s->created_at->toIso8601String(),
                'customer' => $customer,
                'customer_type' => $s->customer_type,
                'employee' => $empName ? [ 'name' => trim($empName) ] : null,
                'employee_name' => $empName ? trim($empName) : null,
                'item_count' => $itemCount,
                'sale_items' => $saleItems,
                'subtotal' => (float)$s->subtotal,
                'tax_amount' => (float)$s->tax_amount,
                'discount_amount' => (float)$s->discount_amount,
                'total_amount' => (float)$s->total_amount,
                'payment_method' => $s->payment_method,
                'payment_reference' => $s->payment_reference,
                'status' => $s->status,
                'source' => 'local',
            ];
        })->unique(function($r){ return $r['sale_number'] ?? ($r['id'] ?? null); })->values()->filter(function($r) use ($existingKeys){
            $k = (string)($r['sale_number'] ?? $r['id'] ?? '');
            return $k !== '' && !in_array($k, $existingKeys, true);
        });

        $finalAll = $merged->merge($localMapped)->values();
        $items = $finalAll->map(function($r){ return is_array($r) ? $r : (array)$r; })
            ->sortBy(function($r){
                try { return \Carbon\Carbon::parse($r['created_at'] ?? now())->timestamp; } catch (\Throwable $e) { return 0; }
            })->values()->all();
        $out = [];
        $indexBySN = [];
        $seenBuckets = [];
        for ($i = 0; $i < count($items); $i++) {
            $r = $items[$i];
            $sn = (string)($r['sale_number'] ?? '');
            $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
            $dt = null; try { $dt = \Carbon\Carbon::parse($r['created_at'] ?? now())->setTimezone($tz); } catch (\Throwable $e) { $dt = now(); }
            $amt = (float)($r['total_amount'] ?? ($r['total'] ?? 0));
            $pm = strtolower((string)($r['payment_method'] ?? ''));
            $bucketKey = $dt->format('Y-m-d H:i') . '|' . number_format($amt, 2, '.', '') . '|' . $pm;

            if ($sn !== '') {
                // De-dup strictly by sale_number; DO NOT drop entries based on bucket when sale_number exists
                if (isset($indexBySN[$sn])) {
                    $keepIdx = $indexBySN[$sn];
                    $keep = $out[$keepIdx];
                    $rSource = strtolower((string)($r['source'] ?? ''));
                    $kSource = strtolower((string)($keep['source'] ?? ''));
                    if ($kSource !== 'local' && $rSource === 'local') { $out[$keepIdx] = $r; }
                    continue;
                } else {
                    // Before inserting new sale_number entry, collapse any near-duplicate already present (different sale_number but same txn)
                    $matchedExisting = false;
                    $ts = 0; try { $ts = $dt->timestamp; } catch (\Throwable $e) { $ts = 0; }
                    for ($j = max(0, count($out) - 100); $j < count($out); $j++) {
                        $p = $out[$j];
                        try {
                            $pd = \Carbon\Carbon::parse($p['created_at'] ?? now())->setTimezone($tz);
                        } catch (\Throwable $e) { $pd = now(); }
                        $pts = $pd->timestamp;
                        $pamt = (float)($p['total_amount'] ?? ($p['total'] ?? 0));
                        $ppm = strtolower((string)($p['payment_method'] ?? ''));
                        if (abs($ts - $pts) <= 600 && abs($amt - $pamt) < 0.01 && $pm === $ppm) {
                            $pHasSN = !empty($p['sale_number']);
                            $rHasSN = !empty($r['sale_number']);
                            $pSource = strtolower((string)($p['source'] ?? ''));
                            $rSource = strtolower((string)($r['source'] ?? ''));
                            $sameSN = $pHasSN && $rHasSN && ((string)$p['sale_number'] === (string)$r['sale_number']);
                            // Only collapse if same sale_number OR cross-source duplicate
                            if ($sameSN || ($pSource !== '' && $rSource !== '' && $pSource !== $rSource)) {
                                // Choose best record: prefer one with non-numeric sale_number; otherwise prefer local source
                                $pSN = (string)($p['sale_number'] ?? '');
                                $rSN = (string)($r['sale_number'] ?? '');
                                $pNumeric = ($pSN !== '') && preg_match('/^\d+$/', $pSN) === 1;
                                $rNumeric = ($rSN !== '') && preg_match('/^\d+$/', $rSN) === 1;
                                $replace = false;
                                if ($rHasSN && !$pHasSN) { $replace = true; }
                                elseif ($pHasSN && !$rHasSN) { $replace = false; }
                                elseif ($pNumeric && !$rNumeric) { $replace = true; }
                                elseif ($rNumeric && !$pNumeric) { $replace = false; }
                                elseif ($pSource !== 'local' && $rSource === 'local') { $replace = true; }
                                if ($replace) { $out[$j] = $r; }
                                $matchedExisting = true;
                                break;
                            }
                        }
                    }
                    if (!$matchedExisting) {
                        $indexBySN[$sn] = count($out);
                        $out[] = $r;
                    }
                    continue;
                }
            }

            $ts = 0; try { $ts = $dt->timestamp; } catch (\Throwable $e) { $ts = 0; }
            // Fallback de-dup only for records WITHOUT sale_number: merge near-duplicates
            $matched = false;
            // Build cart signatures for robust matching
            $cartSig = '';
            try {
                $items = isset($r['sale_items']) && is_array($r['sale_items']) ? $r['sale_items'] : [];
                $sigParts = [];
                foreach ($items as $it) {
                    $sigParts[] = trim(strtolower((string)($it['product_name'] ?? ''))) . 'x' . (int)($it['quantity'] ?? 0) . '@' . number_format((float)($it['unit_price'] ?? 0), 2, '.', '');
                }
                sort($sigParts);
                $cartSig = implode('|', $sigParts);
            } catch (\Throwable $e) { $cartSig = ''; }
            for ($j = max(0, count($out) - 100); $j < count($out); $j++) {
                $p = $out[$j];
                $pd = null; try { $pd = \Carbon\Carbon::parse($p['created_at'] ?? now())->setTimezone($tz); } catch (\Throwable $e) { $pd = now(); }
                $pts = $pd->timestamp;
                $pamt = (float)($p['total_amount'] ?? ($p['total'] ?? 0));
                $ppm = strtolower((string)($p['payment_method'] ?? ''));
                $pSig = '';
                try {
                    $pit = isset($p['sale_items']) && is_array($p['sale_items']) ? $p['sale_items'] : [];
                    $pp = [];
                    foreach ($pit as $it) {
                        $pp[] = trim(strtolower((string)($it['product_name'] ?? ''))) . 'x' . (int)($it['quantity'] ?? 0) . '@' . number_format((float)($it['unit_price'] ?? 0), 2, '.', '');
                    }
                    sort($pp);
                    $pSig = implode('|', $pp);
                } catch (\Throwable $e) { $pSig = ''; }
                if (abs($ts - $pts) <= 600 && abs($amt - $pamt) < 0.01 && $pm === $ppm && $cartSig !== '' && $pSig !== '' && $cartSig === $pSig) {
                    $rHasSN = !empty($r['sale_number']);
                    $pHasSN = !empty($p['sale_number']);
                    $rSource = strtolower((string)($r['source'] ?? ''));
                    $pSource = strtolower((string)($p['source'] ?? ''));
                    $sameSN = $pHasSN && $rHasSN && ((string)$p['sale_number'] === (string)$r['sale_number']);
                    // Only collapse if same sale_number OR cross-source
                    if ($sameSN || ($pSource !== '' && $rSource !== '' && $pSource !== $rSource)) {
                        // Prefer entries with a sale_number, otherwise prefer local source
                        if ($pHasSN && !$rHasSN) {
                            // keep existing $p
                        } elseif ($rHasSN && !$pHasSN) {
                            $out[$j] = $r;
                        } else {
                            if ($pSource !== 'local' && $rSource === 'local') { $out[$j] = $r; }
                        }
                        $matched = true; break;
                    }
                }
            }
            if (!$matched) {
                if (!isset($seenBuckets[$bucketKey])) {
                    $out[] = $r;
                    $seenBuckets[$bucketKey] = strtolower((string)($r['source'] ?? '')) === 'local';
                }
            }
        }
        // Final collapse across sources: dedupe by strong fingerprint within 2 minutes
        $collapsed = [];
        $seenFP = [];
        for ($i = 0; $i < count($out); $i++) {
            $r = $out[$i];
            $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
            $dt = null; try { $dt = Carbon::parse($r['created_at'] ?? now())->setTimezone($tz); } catch (\Throwable $e) { $dt = now(); }
            $amt = (float)($r['total_amount'] ?? ($r['total'] ?? 0));
            $pm = strtolower((string)($r['payment_method'] ?? ''));
            $sig = '';
            try {
                $items = isset($r['sale_items']) && is_array($r['sale_items']) ? $r['sale_items'] : [];
                $parts = [];
                foreach ($items as $it) {
                    $parts[] = trim(strtolower((string)($it['product_name'] ?? ''))) . 'x' . (int)($it['quantity'] ?? 0) . '@' . number_format((float)($it['unit_price'] ?? 0), 2, '.', '');
                }
                sort($parts);
                $sig = implode('|', $parts);
            } catch (\Throwable $e) { $sig = ''; }
            $fp = $dt->format('Y-m-d H:i:s') . '|' . number_format($amt, 2, '.', '') . '|' . $pm . '|' . $sig;
            // Look for existing with same signature within 120s
            $dupeIdx = -1; $preferCurrent = false;
            for ($j = max(0, count($collapsed) - 200); $j < count($collapsed); $j++) {
                $p = $collapsed[$j];
                $pdt = null; try { $pdt = Carbon::parse($p['created_at'] ?? now())->setTimezone($tz); } catch (\Throwable $e) { $pdt = now(); }
                $pamt = (float)($p['total_amount'] ?? ($p['total'] ?? 0));
                $ppm = strtolower((string)($p['payment_method'] ?? ''));
                $psig = '';
                try {
                    $pit = isset($p['sale_items']) && is_array($p['sale_items']) ? $p['sale_items'] : [];
                    $pp = [];
                    foreach ($pit as $it) { $pp[] = trim(strtolower((string)($it['product_name'] ?? ''))) . 'x' . (int)($it['quantity'] ?? 0) . '@' . number_format((float)($it['unit_price'] ?? 0), 2, '.', ''); }
                    sort($pp);
                    $psig = implode('|', $pp);
                } catch (\Throwable $e) { $psig = ''; }
                if (abs($dt->timestamp - $pdt->timestamp) <= 30 && abs($amt - $pamt) < 0.01 && $pm === $ppm && $sig !== '' && $psig !== '' && $sig === $psig) {
                    $pHasSN = !empty($p['sale_number']);
                    $rHasSN = !empty($r['sale_number']);
                    $pSource = strtolower((string)($p['source'] ?? ''));
                    $rSource = strtolower((string)($r['source'] ?? ''));
                    $sameSN = $pHasSN && $rHasSN && ((string)$p['sale_number'] === (string)$r['sale_number']);
                    // Only treat as duplicate if same sale_number OR cross-source
                    if ($sameSN || ($pSource !== '' && $rSource !== '' && $pSource !== $rSource)) {
                        $dupeIdx = $j;
                        if (($rHasSN && !$pHasSN) || ($pSource !== 'local' && $rSource === 'local')) $preferCurrent = true;
                        break;
                    }
                }
            }
            if ($dupeIdx >= 0) {
                if ($preferCurrent) $collapsed[$dupeIdx] = $r;
            } else {
                $collapsed[] = $r;
            }
        }
        $final = collect($collapsed)
            ->map(function($r){ if (is_array($r) && array_key_exists('source',$r)) unset($r['source']); return $r; })
            ->sortByDesc(function($r){ try { return Carbon::parse($r['created_at'] ?? now())->timestamp; } catch (\Throwable $e) { return 0; } })
            ->take(max(1, min(1000, $limit)))
            ->values();
        // Strict final guard: enforce requested date window by local day
        try {
            $startAt = $request->get('start_at');
            $endAt = $request->get('end_at');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $tz = $request->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
            if ($startAt || $endAt) {
                $from = $startAt ? Carbon::parse($startAt) : null;
                $to = $endAt ? Carbon::parse($endAt) : null;
                $final = $final->filter(function($r) use ($from, $to) {
                    try { $c = Carbon::parse($r['created_at'] ?? null); } catch (\Throwable $e) { return true; }
                    if ($from && $c->lt($from)) return false;
                    if ($to && $c->gt($to)) return false;
                    return true;
                })->values();
            } elseif ($dateFrom || $dateTo) {
                $from = $dateFrom ? Carbon::parse($dateFrom, $tz)->startOfDay() : null;
                $to = $dateTo ? Carbon::parse($dateTo, $tz)->endOfDay() : null;
                $final = $final->filter(function($r) use ($from, $to, $tz) {
                    try { $c = Carbon::parse($r['created_at'] ?? null)->setTimezone($tz); } catch (\Throwable $e) { return true; }
                    if ($from && $c->lt($from)) return false;
                    if ($to && $c->gt($to)) return false;
                    return true;
                })->values();
            }
        } catch (\Throwable $e) { /* ignore */ }
        return response()->json($final);
    }

    private function verifyEmployeePin($pin)
    {
        // Implement PIN verification logic
        // For now, accept any PIN for demo purposes
        return !empty($pin);
    }
    
    private function generateRefundSaleNumber($originalSaleNumber)
    {
        return $originalSaleNumber . '-R' . now()->format('His');
    }
    
    private function getSalesAnalytics($request)
    {
        // Default Eloquent analytics
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
        $tz = request()->get('tz', config('app.timezone') ?: date_default_timezone_get() ?: 'UTC');
        $startUtc = Carbon::parse($dateFrom, $tz)->startOfDay()->setTimezone('UTC');
        $endUtc = Carbon::parse($dateTo, $tz)->endOfDay()->setTimezone('UTC');

        $sales = Sale::whereBetween('created_at', [$startUtc, $endUtc])
            ->where('status', 'completed');

        return [
            'totalSales' => (clone $sales)->sum('total_amount'),
            'totalTransactions' => (clone $sales)->count(),
            'averageOrderValue' => (clone $sales)->avg('total_amount'),
            'totalTax' => (clone $sales)->sum('tax_amount'),
            'totalItems' => (clone $sales)->withSum('saleItems', 'quantity')->sum('sale_items_sum_quantity'),
            'paymentBreakdown' => [
                'cash' => (clone $sales)->where('payment_method', 'cash')->sum('total_amount'),
                'debit' => (clone $sales)->where('payment_method', 'debit')->sum('total_amount'),
                'credit' => (clone $sales)->where('payment_method', 'credit')->sum('total_amount'),
            ]
        ];
    }

    private function getSalesAnalyticsFromArray(Collection $mapped, Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $rows = $mapped;
        if ($dateFrom) {
            $rows = $rows->filter(fn($r) => $r->created_at->format('Y-m-d') >= $dateFrom);
        }
        if ($dateTo) {
            $rows = $rows->filter(fn($r) => $r->created_at->format('Y-m-d') <= $dateTo);
        }
        $completed = $rows->filter(fn($r) => ($r->status ?? 'completed') === 'completed');
        $totalSales = $completed->sum(fn($r) => (float)($r->total_amount ?? 0));
        $totalTransactions = $completed->count();
        $averageOrderValue = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        $totalTax = $completed->sum(fn($r) => (float)($r->tax_amount ?? 0));
        $totalItems = $completed->sum(fn($r) => (int)($r->item_count ?? 0));
        $paymentBreakdown = [
            'cash' => $completed->filter(fn($r) => ($r->payment_method ?? '') === 'cash')->sum(fn($r) => (float)($r->total_amount ?? 0)),
            'debit' => $completed->filter(fn($r) => ($r->payment_method ?? '') === 'debit')->sum(fn($r) => (float)($r->total_amount ?? 0)),
            'credit' => $completed->filter(fn($r) => ($r->payment_method ?? '') === 'credit')->sum(fn($r) => (float)($r->total_amount ?? 0)),
        ];
        return compact('totalSales','totalTransactions','averageOrderValue','totalTax','totalItems','paymentBreakdown');
    }
    
    private function generateDailyReportData($sales, $date)
    {
        $totalSales = $sales->sum('total_amount');
        $totalTax = $sales->sum('tax_amount');
        $transactionCount = $sales->count();
        
        // Employee breakdown
        $employeeBreakdown = $sales->groupBy('employee_id')->map(function($employeeSales) {
            $employee = $employeeSales->first()->employee;
            return [
                'name' => $employee ? $employee->full_name : 'Unknown',
                'sales' => $employeeSales->sum('total_amount'),
                'transactions' => $employeeSales->count()
            ];
        });
        
        // Payment method breakdown
        $paymentBreakdown = $sales->groupBy('payment_method')->map(function($paymentSales, $method) {
            return [
                'method' => ucfirst($method),
                'amount' => $paymentSales->sum('total_amount'),
                'count' => $paymentSales->count()
            ];
        });
        
        // Hourly breakdown
        $hourlyBreakdown = $sales->groupBy(function($sale) {
            return $sale->created_at->format('H:00');
        })->map(function($hourlySales, $hour) {
            return [
                'hour' => $hour,
                'sales' => $hourlySales->sum('total_amount'),
                'transactions' => $hourlySales->count()
            ];
        })->sortKeys();
        
        return [
            'date' => $date,
            'totalSales' => $totalSales,
            'totalTax' => $totalTax,
            'transactionCount' => $transactionCount,
            'averageOrderValue' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
            'employeeBreakdown' => $employeeBreakdown,
            'paymentBreakdown' => $paymentBreakdown,
            'hourlyBreakdown' => $hourlyBreakdown
        ];
    }
    
    private function generateWeeklyReportData($sales, $startDate, $endDate)
    {
        // Similar to daily but with daily breakdowns
        $dailyBreakdown = $sales->groupBy(function($sale) {
            return $sale->created_at->format('Y-m-d');
        })->map(function($dailySales, $date) {
            return [
                'date' => $date,
                'sales' => $dailySales->sum('total_amount'),
                'transactions' => $dailySales->count()
            ];
        })->sortKeys();
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalSales' => $sales->sum('total_amount'),
            'totalTax' => $sales->sum('tax_amount'),
            'transactionCount' => $sales->count(),
            'dailyBreakdown' => $dailyBreakdown
        ];
    }
    
    private function generateMonthlyReportData($sales, $month)
    {
        // Comprehensive monthly analysis
        $totalSales = $sales->sum('total_amount');
        $totalTax = $sales->sum('tax_amount');
        $transactionCount = $sales->count();
        
        // Top products
        $topProducts = $sales->flatMap->saleItems
            ->groupBy('product_id')
            ->map(function($items) {
                $product = $items->first()->product;
                return [
                    'name' => $product ? $product->name : 'Unknown',
                    'quantity' => $items->sum('quantity'),
                    'revenue' => $items->sum('total_price')
                ];
            })
            ->sortByDesc('revenue')
            ->take(10);
        
        // Customer analysis
        $newCustomers = $sales->whereNotNull('customer_id')
            ->pluck('customer_id')
            ->unique()
            ->count();
        
        return [
            'month' => $month,
            'totalSales' => $totalSales,
            'totalTax' => $totalTax,
            'transactionCount' => $transactionCount,
            'averageOrderValue' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
            'topProducts' => $topProducts,
            'newCustomers' => $newCustomers
        ];
    }
}

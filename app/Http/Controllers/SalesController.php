<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        
        $query = Sale::with(['customer', 'employee', 'saleItems.product']);
        
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
        
        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        $sales = $query->paginate(20);
        
        // Get filter options
        $employees = Employee::all();
        $paymentMethods = Sale::distinct('payment_method')->pluck('payment_method');
        
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
    
    public function receipt($id)
    {
        $sale = Sale::with([
            'customer',
            'employee',
            'saleItems.product'
        ])->findOrFail($id);
        
        // Generate receipt PDF
        $pdf = PDF::loadView('sales.receipt', compact('sale'));
        
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
        
        // Generate receipt PDF
        $pdf = PDF::loadView('sales.receipt', [
            'sale' => $sale,
            'reprint' => true
        ]);
        
        return $pdf->download("receipt_{$sale->sale_number}_reprint.pdf");
    }
    
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        $sales = Sale::with(['employee', 'saleItems'])
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();
        
        $report = $this->generateDailyReportData($sales, $date);
        
        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('sales.reports.daily', compact('report', 'date'));
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
            $pdf = PDF::loadView('sales.reports.weekly', compact('report', 'startDate', 'endDate'));
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
            $pdf = PDF::loadView('sales.reports.monthly', compact('report', 'month'));
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
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
        
        $sales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed');
        
        return [
            'totalSales' => $sales->sum('total_amount'),
            'totalTransactions' => $sales->count(),
            'averageOrderValue' => $sales->avg('total_amount'),
            'totalTax' => $sales->sum('tax_amount'),
            'totalItems' => $sales->withSum('saleItems', 'quantity')->sum('sale_items_sum_quantity'),
            'paymentBreakdown' => [
                'cash' => $sales->where('payment_method', 'cash')->sum('total_amount'),
                'debit' => $sales->where('payment_method', 'debit')->sum('total_amount'),
                'credit' => $sales->where('payment_method', 'credit')->sum('total_amount')
            ]
        ];
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

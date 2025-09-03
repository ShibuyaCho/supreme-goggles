<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }
    
    public function inventoryEvaluation(Request $request)
    {
        $products = Product::all();
        
        $categories = $products->groupBy('category')->map(function($categoryProducts, $category) {
            $totalCost = $categoryProducts->sum(function($product) {
                return $product->cost * $product->quantity;
            });
            
            $totalValue = $categoryProducts->sum(function($product) {
                return $product->price * $product->quantity;
            });
            
            return [
                'category' => $category,
                'items' => $categoryProducts->count(),
                'total_cost' => $totalCost,
                'total_value' => $totalValue,
                'margin' => $totalValue > 0 ? (($totalValue - $totalCost) / $totalValue) * 100 : 0
            ];
        });
        
        $totals = [
            'total_items' => $products->count(),
            'total_cost' => $categories->sum('total_cost'),
            'total_value' => $categories->sum('total_value'),
            'overall_margin' => 0
        ];
        
        $totals['overall_margin'] = $totals['total_value'] > 0 
            ? (($totals['total_value'] - $totals['total_cost']) / $totals['total_value']) * 100 
            : 0;
        
        return view('reports.inventory-evaluation', compact('categories', 'totals'));
    }
    
    public function salesReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');
        
        $salesQuery = Sale::whereBetween('created_at', [$startDate, $endDate])
                         ->where('status', 'completed');
        
        $salesData = match($groupBy) {
            'day' => $salesQuery->selectRaw('DATE(created_at) as period, COUNT(*) as transactions, SUM(total) as revenue')
                               ->groupBy('period'),
            'week' => $salesQuery->selectRaw('YEARWEEK(created_at) as period, COUNT(*) as transactions, SUM(total) as revenue')
                                ->groupBy('period'),
            'month' => $salesQuery->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as transactions, SUM(total) as revenue')
                                 ->groupBy('period'),
            default => $salesQuery->selectRaw('DATE(created_at) as period, COUNT(*) as transactions, SUM(total) as revenue')
                                 ->groupBy('period')
        };
        
        $results = $salesData->orderBy('period')->get();
        
        return response()->json($results);
    }
    
    public function customerReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $customerData = Customer::withCount(['sales' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'completed');
        }])
        ->with(['sales' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'completed');
        }])
        ->having('sales_count', '>', 0)
        ->get()
        ->map(function($customer) {
            return [
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'customer_type' => $customer->customer_type,
                'visits' => $customer->sales_count,
                'total_spent' => $customer->sales->sum('total'),
                'avg_order' => $customer->sales_count > 0 ? $customer->sales->sum('total') / $customer->sales_count : 0
            ];
        })
        ->sortByDesc('total_spent')
        ->values();
        
        return response()->json($customerData);
    }
    
    public function productPerformance(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $productData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->select(
                'products.name',
                'products.category',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total) as revenue'),
                DB::raw('AVG(sale_items.price) as avg_price')
            )
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderBy('revenue', 'desc')
            ->limit(50)
            ->get();
        
        return response()->json($productData);
    }
    
    public function export(Request $request)
    {
        $reportType = $request->get('type');
        $format = $request->get('format', 'csv');
        
        switch ($reportType) {
            case 'inventory':
                return $this->exportInventoryReport($format);
            case 'sales':
                return $this->exportSalesReport($request, $format);
            case 'customers':
                return $this->exportCustomerReport($request, $format);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }
    
    private function exportInventoryReport($format)
    {
        $products = Product::all();
        
        $filename = 'inventory_evaluation_' . now()->format('Y-m-d') . '.' . $format;
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Product Name',
                'Category',
                'Quantity',
                'Unit Cost',
                'Unit Price',
                'Total Cost',
                'Total Value',
                'Margin %'
            ]);
            
            foreach ($products as $product) {
                $totalCost = $product->cost * $product->quantity;
                $totalValue = $product->price * $product->quantity;
                $margin = $totalValue > 0 ? (($totalValue - $totalCost) / $totalValue) * 100 : 0;
                
                fputcsv($file, [
                    $product->name,
                    $product->category,
                    $product->quantity,
                    $product->cost,
                    $product->price,
                    $totalCost,
                    $totalValue,
                    round($margin, 2)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportSalesReport($request, $format)
    {
        // Implementation for sales report export
        return response()->json(['message' => 'Sales report export not yet implemented']);
    }
    
    private function exportCustomerReport($request, $format)
    {
        // Implementation for customer report export
        return response()->json(['message' => 'Customer report export not yet implemented']);
    }
}

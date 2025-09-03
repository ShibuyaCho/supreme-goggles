<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Room;
use App\Models\PriceTier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $filterCategory = $request->get('category', 'all');
        $filterRoom = $request->get('room', 'all');
        $filterStatus = $request->get('status', 'all');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $viewMode = $request->get('view_mode', 'grid');
        $selectedTab = $request->get('tab', 'products');
        
        $query = Product::query();
        
        // Apply search filter
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('sku', 'like', "%{$searchQuery}%")
                  ->orWhere('strain', 'like', "%{$searchQuery}%")
                  ->orWhere('metrc_tag', 'like', "%{$searchQuery}%")
                  ->orWhere('batch_id', 'like', "%{$searchQuery}%");
            });
        }
        
        // Apply category filter
        if ($filterCategory !== 'all') {
            $query->where('category', $filterCategory);
        }
        
        // Apply room filter
        if ($filterRoom !== 'all') {
            $query->where('room', $filterRoom);
        }
        
        // Apply status filter
        switch ($filterStatus) {
            case 'in_stock':
                $query->where('quantity', '>', 0);
                break;
            case 'low_stock':
                $query->whereColumn('quantity', '<=', 'reorder_point')
                      ->orWhere(function($q) {
                          $q->whereNull('reorder_point')->where('quantity', '<=', 10);
                      });
                break;
            case 'out_of_stock':
                $query->where('quantity', '<=', 0);
                break;
            case 'expired':
                $query->where('expiration_date', '<', now());
                break;
            case 'expiring_soon':
                $query->whereBetween('expiration_date', [now(), now()->addDays(30)]);
                break;
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
        
        $products = $query->paginate(20);
        
        // Get filter options
        $categories = Product::distinct('category')->pluck('category');
        $rooms = Room::all();
        
        // Get analytics data
        $analytics = $this->getProductAnalytics();
        
        return view('products.index', compact(
            'products',
            'searchQuery',
            'filterCategory',
            'filterRoom',
            'filterStatus',
            'sortBy',
            'sortOrder',
            'viewMode',
            'selectedTab',
            'categories',
            'rooms',
            'analytics'
        ));
    }
    
    public function create()
    {
        $categories = Product::distinct('category')->pluck('category');
        $rooms = Room::all();
        $strains = Product::distinct('strain')->whereNotNull('strain')->pluck('strain');
        $suppliers = Product::distinct('supplier')->whereNotNull('supplier')->pluck('supplier');
        
        return view('products.create', compact('categories', 'rooms', 'strains', 'suppliers'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'room' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'weight' => 'nullable|string|max:50',
            'strain' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'metrc_tag' => 'nullable|string|max:255|unique:products,metrc_tag',
            'batch_id' => 'nullable|string|max:255',
            'thc' => 'nullable|numeric|min:0|max:100',
            'cbd' => 'nullable|numeric|min:0|max:100',
            'cbg' => 'nullable|numeric|min:0|max:100',
            'cbn' => 'nullable|numeric|min:0|max:100',
            'cbc' => 'nullable|numeric|min:0|max:100',
            'thc_mg' => 'nullable|numeric|min:0',
            'cbd_mg' => 'nullable|numeric|min:0',
            'harvest_date' => 'nullable|date',
            'packaged_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:today',
            'lab_name' => 'nullable|string|max:255',
            'test_date' => 'nullable|date',
            'reorder_point' => 'nullable|integer|min:0',
            'minimum_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'is_gls' => 'boolean',
            'is_untaxed' => 'boolean',
            'administrative_hold' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $productData = $request->except(['image']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = Storage::url($imagePath);
        }
        
        // Generate SKU if not provided
        if (empty($productData['sku'])) {
            $productData['sku'] = $this->generateSKU($request->category, $request->name);
        }
        
        // Set test status based on lab data
        if ($request->lab_name && $request->test_date) {
            $productData['test_status'] = 'passed';
            $productData['is_tested'] = true;
            $productData['contaminants_passed'] = true;
        }
        
        $product = Product::create($productData);
        
        return redirect()->route('products.index')
                        ->with('success', 'Product created successfully');
    }
    
    public function show($id)
    {
        $product = Product::with(['saleItems.sale', 'room'])->findOrFail($id);
        
        // Get sales history
        $salesHistory = $product->saleItems()
            ->with('sale')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get inventory movements (would need to implement inventory_movements table)
        $inventoryMovements = collect([]); // Placeholder
        
        // Calculate analytics
        $analytics = [
            'totalSold' => $product->total_sold,
            'revenueGenerated' => $product->revenue_generated,
            'averagePrice' => $product->saleItems()->avg('unit_price'),
            'popularityScore' => $product->popularity_score,
            'daysInStock' => $product->created_at->diffInDays(now()),
            'turnoverRate' => $this->calculateTurnoverRate($product)
        ];
        
        return view('products.show', compact('product', 'salesHistory', 'inventoryMovements', 'analytics'));
    }
    
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Product::distinct('category')->pluck('category');
        $rooms = Room::all();
        $strains = Product::distinct('strain')->whereNotNull('strain')->pluck('strain');
        $suppliers = Product::distinct('supplier')->whereNotNull('supplier')->pluck('supplier');
        
        return view('products.edit', compact('product', 'categories', 'rooms', 'strains', 'suppliers'));
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'room' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'weight' => 'nullable|string|max:50',
            'strain' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'metrc_tag' => 'nullable|string|max:255|unique:products,metrc_tag,' . $id,
            'batch_id' => 'nullable|string|max:255',
            'thc' => 'nullable|numeric|min:0|max:100',
            'cbd' => 'nullable|numeric|min:0|max:100',
            'cbg' => 'nullable|numeric|min:0|max:100',
            'cbn' => 'nullable|numeric|min:0|max:100',
            'cbc' => 'nullable|numeric|min:0|max:100',
            'thc_mg' => 'nullable|numeric|min:0',
            'cbd_mg' => 'nullable|numeric|min:0',
            'harvest_date' => 'nullable|date',
            'packaged_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'lab_name' => 'nullable|string|max:255',
            'test_date' => 'nullable|date',
            'reorder_point' => 'nullable|integer|min:0',
            'minimum_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'is_gls' => 'boolean',
            'is_untaxed' => 'boolean',
            'administrative_hold' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $productData = $request->except(['image']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                $oldImagePath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($oldImagePath);
            }
            
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = Storage::url($imagePath);
        }
        
        $product->update($productData);
        
        return redirect()->route('products.index')
                        ->with('success', 'Product updated successfully');
    }
    
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product has sales
        if ($product->saleItems()->exists()) {
            return response()->json([
                'error' => 'Cannot delete product with existing sales records'
            ], 400);
        }
        
        // Delete image
        if ($product->image) {
            $imagePath = str_replace('/storage/', '', $product->image);
            Storage::disk('public')->delete($imagePath);
        }
        
        $product->delete();
        
        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
    
    public function transferRoom(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'new_room' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $oldRoom = $product->room;
        $product->update(['room' => $request->new_room]);
        
        // Log the transfer (would implement audit log)
        
        return response()->json([
            'message' => "Product transferred from {$oldRoom} to {$request->new_room}",
            'product' => $product
        ]);
    }
    
    public function adjustQuantity(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $oldQuantity = $product->quantity;
        
        switch ($request->adjustment_type) {
            case 'add':
                $newQuantity = $oldQuantity + $request->quantity;
                break;
            case 'subtract':
                $newQuantity = max(0, $oldQuantity - $request->quantity);
                break;
            case 'set':
                $newQuantity = $request->quantity;
                break;
        }
        
        $product->update(['quantity' => $newQuantity]);
        
        // Log the adjustment (would implement inventory log)
        
        return response()->json([
            'message' => 'Quantity adjusted successfully',
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'product' => $product
        ]);
    }
    
    public function generateBarcode($id)
    {
        $product = Product::findOrFail($id);
        
        // Generate barcode PDF
        $pdf = PDF::loadView('products.barcode', compact('product'));
        
        return $pdf->download("barcode_{$product->sku}.pdf");
    }
    
    public function generateLabel($id)
    {
        $product = Product::findOrFail($id);
        
        // Generate exit label PDF
        $pdf = PDF::loadView('products.exit-label', compact('product'));
        
        return $pdf->download("exit_label_{$product->metrc_tag}.pdf");
    }
    
    public function bulkTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'new_room' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $products = Product::whereIn('id', $request->product_ids)->get();
        
        foreach ($products as $product) {
            $product->update(['room' => $request->new_room]);
        }
        
        return response()->json([
            'message' => count($products) . ' products transferred to ' . $request->new_room
        ]);
    }
    
    public function export(Request $request)
    {
        // Apply same filters as index
        $query = Product::query();
        
        // ... filter logic similar to index method
        
        $products = $query->get();
        
        $filename = 'products_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'SKU', 'Name', 'Category', 'Price', 'Cost', 'Quantity', 'Room',
                'THC%', 'CBD%', 'Strain', 'METRC Tag', 'Batch ID', 'Created Date'
            ]);
            
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->category,
                    $product->price,
                    $product->cost ?? 0,
                    $product->quantity,
                    $product->room,
                    $product->thc ?? 0,
                    $product->cbd ?? 0,
                    $product->strain ?? '',
                    $product->metrc_tag ?? '',
                    $product->batch_id ?? '',
                    $product->created_at->format('Y-m-d')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function generateSKU($category, $name)
    {
        $categoryCode = strtoupper(substr($category, 0, 3));
        $nameCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $number = str_pad(Product::count() + 1, 4, '0', STR_PAD_LEFT);
        
        return $categoryCode . '-' . $nameCode . '-' . $number;
    }
    
    private function calculateTurnoverRate($product)
    {
        $totalSold = $product->total_sold;
        $averageInventory = ($product->quantity + $totalSold) / 2;
        
        return $averageInventory > 0 ? $totalSold / $averageInventory : 0;
    }
    
    private function getProductAnalytics()
    {
        return [
            'total' => Product::count(),
            'inStock' => Product::where('quantity', '>', 0)->count(),
            'lowStock' => Product::whereColumn('quantity', '<=', 'reorder_point')->count(),
            'outOfStock' => Product::where('quantity', '<=', 0)->count(),
            'expiring' => Product::whereBetween('expiration_date', [now(), now()->addDays(30)])->count(),
            'totalValue' => Product::selectRaw('SUM(price * quantity) as total')->value('total') ?? 0,
            'averagePrice' => Product::avg('price') ?? 0,
            'categories' => Product::distinct('category')->count()
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PDF;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('search', '');
        $filterCategory = $request->get('category', 'all');
        $filterRoom = $request->get('room', 'all');
        $filterStatus = $request->get('status', 'all');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $viewMode = $request->get('view_mode', 'grid');
        $selectedTab = $request->get('tab', 'products');

        $query = Product::query();

        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('sku', 'like', "%{$searchQuery}%")
                  ->orWhere('strain', 'like', "%{$searchQuery}%")
                  ->orWhere('metrc_tag', 'like', "%{$searchQuery}%");
            });
        }

        if ($filterCategory !== 'all') {
            $query->where('category', $filterCategory);
        }

        if ($filterRoom !== 'all') {
            $query->where('room', $filterRoom);
        }

        if ($filterStatus !== 'all') {
            if ($filterStatus === 'in_stock') {
                $query->where('quantity', '>', 0);
            } elseif ($filterStatus === 'low_stock') {
                $query->whereColumn('quantity', '<=', 'reorder_point');
            } elseif ($filterStatus === 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            } elseif ($filterStatus === 'expired') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '<', now());
            } elseif ($filterStatus === 'expiring_soon') {
                $query->whereNotNull('expiration_date')->whereBetween('expiration_date', [now(), now()->addDays(30)]);
            }
        }

        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(24);
        $categories = Product::select('category')->distinct()->pluck('category');
        $rooms = Room::all();

        // Simple analytics placeholders
        $analytics = [
            'total' => Product::count(),
            'inStock' => Product::where('quantity', '>', 0)->count(),
            'lowStock' => Product::whereColumn('quantity', '<=', 'reorder_point')->count(),
            'outOfStock' => Product::where('quantity', '<=', 0)->count(),
            'totalValue' => Product::sum('price'),
            'averagePrice' => (float) Product::avg('price'),
        ];

        return view('products.index', compact(
            'products', 'categories', 'rooms', 'searchQuery', 'filterCategory', 'filterRoom', 'filterStatus', 'sortBy', 'sortOrder', 'viewMode', 'selectedTab', 'analytics'
        ));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $salesHistory = collect([]);
        $inventoryMovements = collect([]);
        $analytics = [
            'turnover_rate' => $this->calculateTurnoverRate($product),
            'days_in_inventory' => now()->diffInDays($product->created_at),
        ];

        return view('products.show', compact('product', 'salesHistory', 'inventoryMovements', 'analytics'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = Storage::url($imagePath);
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $productData = $request->except(['image']);

        if ($request->hasFile('image')) {
            if ($product->image) {
                $oldImagePath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($oldImagePath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = Storage::url($imagePath);
        }

        $product->update($productData);

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->saleItems()->exists()) {
            return response()->json([
                'error' => 'Cannot delete product with existing sales records'
            ], 400);
        }

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

        return response()->json([
            'message' => 'Quantity adjusted successfully',
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'product' => $product
        ]);
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

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'message' => 'Invalid request'], 422);
        }

        $ids = $request->input('product_ids', []);
        $products = Product::whereIn('id', $ids)->get();

        $deleted = 0;
        $skipped = [];

        foreach ($products as $product) {
            if ($product->saleItems()->exists()) {
                $skipped[] = $product->name;
                continue;
            }
            if ($product->image) {
                $imagePath = str_replace('/storage/', '', $product->image);
                Storage::disk('public')->delete($imagePath);
            }
            $product->delete();
            $deleted++;
        }

        $message = $deleted . ' products deleted';
        if (!empty($skipped)) {
            $message .= '. Skipped: ' . implode(', ', $skipped);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'skipped' => $skipped,
        ]);
    }

    public function updatePricing(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $product->update(['price' => $request->price]);
        return response()->json(['message' => 'Pricing updated', 'product' => $product]);
    }

    public function bulkPricing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'price' => 'required|numeric|min:0'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        Product::whereIn('id', $request->product_ids)->update(['price' => $request->price]);
        return response()->json(['message' => count($request->product_ids) . ' products updated']);
    }

    public function generateBarcode($id)
    {
        $product = Product::findOrFail($id);
        $pdf = PDF::loadView('products.barcode', compact('product'));
        return $pdf->download("barcode_{$product->sku}.pdf");
    }

    public function generateLabel($id)
    {
        $product = Product::findOrFail($id);
        $pdf = PDF::loadView('products.exit-label', compact('product'));
        return $pdf->download("exit_label_{$product->metrc_tag}.pdf");
    }

    public function export(Request $request)
    {
        $query = Product::query();
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
        return response()->streamDownload($callback, $filename, $headers);
    }

    public function import(Request $request)
    {
        return back()->with('status', 'Import endpoint not configured');
    }

    private function calculateTurnoverRate(Product $product): float
    {
        $totalSold = $product->total_sold;
        $averageInventory = ($product->quantity + $totalSold) / 2;
        return $averageInventory > 0 ? $totalSold / $averageInventory : 0.0;
    }
}

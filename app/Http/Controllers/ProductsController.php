<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Room;
use App\Services\MetrcService;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        // Categories for filtering (existing product categories only)
        $categories = Product::select('category')->distinct()->pluck('category');
        $rooms = Room::all();

        // Categories for the Create Product modal (METRC + business-specific)
        $createCategories = [];
        try {
            $metrcCategories = app(MetrcService::class)->getItemCategories();
            if (is_array($metrcCategories) && isset($metrcCategories[0]) && is_array($metrcCategories[0]) && (isset($metrcCategories[0]['Name']) || isset($metrcCategories[0]['name']))) {
                $metrcCategories = collect($metrcCategories)->map(fn($c) => $c['Name'] ?? $c['name'])->values()->all();
            }
            $additional = ['Plants (Clones)', 'Apparel', 'Extracts', 'Inhalable Cannabinoid', 'Patches', 'Seeds'];
            $createCategories = collect(array_merge($metrcCategories, $additional))
                ->filter(fn($c) => is_string($c) && trim($c) !== '')
                ->map(fn($c) => trim($c))
                ->unique()
                ->sort()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            $createCategories = ['Flower','Pre-Rolls','Concentrates','Extracts','Edibles','Topicals','Tinctures','Vape Cartridges','Vape Pens','Inhalable Cannabinoids','Clones','Immature Plants','Seeds','Accessories'];
        }

        // Simple analytics placeholders
        $analytics = [
            'total' => Product::count(),
            'inStock' => Product::where('quantity', '>', 0)->count(),
            'lowStock' => Product::whereColumn('quantity', '<=', 'reorder_point')->count(),
            'outOfStock' => Product::where('quantity', '<=', 0)->count(),
            'totalValue' => Product::sum('price'),
            'averagePrice' => (float) Product::avg('price'),
        ];

        // JSON for API requests
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        }

        return view('products.index', compact(
            'products', 'categories', 'rooms', 'searchQuery', 'filterCategory', 'filterRoom', 'filterStatus', 'sortBy', 'sortOrder', 'viewMode', 'selectedTab', 'analytics', 'createCategories'
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
        // Load METRC categories with fallback and include business-specific categories
        $metrcCategories = [];
        try {
            $metrcCategories = app(MetrcService::class)->getItemCategories();
            if (is_array($metrcCategories) && isset($metrcCategories[0]) && is_array($metrcCategories[0]) && (isset($metrcCategories[0]['Name']) || isset($metrcCategories[0]['name']))) {
                $metrcCategories = collect($metrcCategories)->map(fn($c) => $c['Name'] ?? $c['name'])->values()->all();
            }
        } catch (\Throwable $e) {
            $metrcCategories = [];
        }

        $additional = ['Plants (Clones)', 'Apparel', 'Extracts', 'Inhalable Cannabinoid', 'Patches', 'Seeds'];
        $categories = collect(array_merge($metrcCategories, $additional))
            ->filter(fn($c) => is_string($c) && trim($c) !== '')
            ->map(fn($c) => trim($c))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('products.create', compact('categories'));
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

        // Normalize payload for Supabase
        $sbPayload = $data;
        $sbPayload['created_at'] = now()->toIso8601String();
        $sbPayload['updated_at'] = now()->toIso8601String();

        $supa = app(SupabaseService::class);
        if ($supa->enabled()) {
            $resp = $supa->insert('products', [ $sbPayload ], ['prefer' => 'return=representation']);
            if ($resp['ok'] ?? false) {
                $rows = $resp['data'];
                $created = is_array($rows) && isset($rows[0]) ? $rows[0] : $rows;
                // Mirror to local DB using returned representation
                $product = Product::create($this->mapSupabaseProductToLocal($created));
                return redirect()->route('products.index')->with('success', 'Product created successfully');
            }
            if (($resp['error'] ?? null) === 'RLS_DENIED') {
                return back()->withErrors(['error' => 'Supabase rejected the write due to Row Level Security. Please check policies for products.'])->withInput();
            }
            Log::warning('Supabase create product failed, falling back to local DB', ['status' => $resp['status'] ?? 0, 'error' => $resp['error'] ?? null]);
        }

        // Fallback: create locally
        $product = Product::create($data);
        return redirect()->route('products.index')->with('success', 'Product created locally (remote sync pending)');
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

        // Supabase-first update
        $supa = app(SupabaseService::class);
        if ($supa->enabled()) {
            $payload = $productData;
            $payload['updated_at'] = now()->toIso8601String();
            $resp = $supa->update('products', ['id' => $product->id], $payload, ['prefer' => 'return=representation']);
            if ($resp['ok'] ?? false) {
                $rows = $resp['data'];
                $updated = is_array($rows) && isset($rows[0]) ? $rows[0] : $rows;
                $product->update($this->mapSupabaseProductToLocal($updated));
                return redirect()->route('products.index')->with('success', 'Product updated successfully');
            }
            if (($resp['error'] ?? null) === 'RLS_DENIED') {
                return back()->withErrors(['error' => 'Supabase rejected the update due to Row Level Security. Please check policies for products.'])->withInput();
            }
            Log::warning('Supabase update product failed, falling back to local DB', ['status' => $resp['status'] ?? 0, 'error' => $resp['error'] ?? null]);
        }

        // Fallback: local update
        $product->update($productData);
        return redirect()->route('products.index')->with('success', 'Product updated locally (remote sync pending)');
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

        // Supabase-first delete
        $supa = app(SupabaseService::class);
        if ($supa->enabled()) {
            $resp = $supa->delete('products', ['id' => $product->id]);
            if (($resp['ok'] ?? false) || ($resp['status'] ?? 0) === 404) {
                $product->delete();
                return response()->json(['message' => 'Product deleted successfully']);
            }
            if (($resp['error'] ?? null) === 'RLS_DENIED') {
                return response()->json(['error' => 'Supabase rejected the delete due to Row Level Security. Please check policies for products.'], 403);
            }
            Log::warning('Supabase delete product failed, falling back to local DB', ['status' => $resp['status'] ?? 0, 'error' => $resp['error'] ?? null]);
        }

        // Fallback: local delete
        $product->delete();
        return response()->json(['message' => 'Product deleted locally (remote sync pending)']);
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

        // Mirror to Supabase (product room + inventory movement)
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/products?id=eq.' . urlencode($product->id), [ 'room' => $request->new_room, 'updated_at' => now()->toISOString() ]);
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/inventory_movements', [[
                    'product_id' => (int)$product->id,
                    'from_room' => $oldRoom,
                    'to_room' => $request->new_room,
                    'quantity' => null,
                    'reason' => $request->reason ?: 'transfer',
                    'actor' => auth()->user()->email ?? 'system',
                    'created_at' => now()->toISOString(),
                ]]);
            }
        } catch (\Throwable $e) { /* ignore supabase mirror failures */ }

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

        // Mirror to Supabase (quantity + movement log)
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/products?id=eq.' . urlencode($product->id), [ 'quantity' => $newQuantity, 'updated_at' => now()->toISOString() ]);
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/inventory_movements', [[
                    'product_id' => (int)$product->id,
                    'from_room' => $product->room,
                    'to_room' => $product->room,
                    'quantity' => (int)($newQuantity - $oldQuantity),
                    'reason' => $request->reason ?: 'adjust',
                    'actor' => auth()->user()->email ?? 'system',
                    'created_at' => now()->toISOString(),
                ]]);
            }
        } catch (\Throwable $e) { /* ignore supabase mirror failures */ }

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

        // Mirror to Supabase in batch (by filter)
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                $ids = implode(',', array_map('intval', $request->product_ids));
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/products?id=in.(' . $ids . ')', [ 'room' => $request->new_room, 'updated_at' => now()->toISOString() ]);
            }
        } catch (\Throwable $e) { /* ignore supabase mirror failures */ }

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

        // Mirror to Supabase in batch
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                $ids = implode(',', array_map('intval', $request->product_ids));
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/products?id=in.(' . $ids . ')', [ 'price' => $request->price, 'updated_at' => now()->toISOString() ]);
            }
        } catch (\Throwable $e) { /* ignore supabase mirror failures */ }

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

    private function mapSupabaseProductToLocal(array $row): array
    {
        // Map Supabase product representation to local columns. Unknown keys are ignored by mass assignment if not fillable.
        return [
            'id' => $row['id'] ?? null,
            'name' => $row['name'] ?? null,
            'category' => $row['category'] ?? null,
            'price' => $row['price'] ?? null,
            'cost' => $row['cost'] ?? null,
            'quantity' => $row['quantity'] ?? ($row['stock'] ?? null),
            'stock' => $row['stock'] ?? ($row['quantity'] ?? null),
            'room' => $row['room'] ?? null,
            'sku' => $row['sku'] ?? null,
            'weight' => $row['weight'] ?? null,
            'thc' => $row['thc'] ?? null,
            'cbd' => $row['cbd'] ?? null,
            'cbg' => $row['cbg'] ?? null,
            'cbn' => $row['cbn'] ?? null,
            'cbc' => $row['cbc'] ?? null,
            'thc_mg' => $row['thc_mg'] ?? null,
            'cbd_mg' => $row['cbd_mg'] ?? null,
            'cbg_mg' => $row['cbg_mg'] ?? null,
            'cbn_mg' => $row['cbn_mg'] ?? null,
            'cbc_mg' => $row['cbc_mg'] ?? null,
            'strain' => $row['strain'] ?? null,
            'metrc_tag' => $row['metrc_tag'] ?? null,
            'batch_id' => $row['batch_id'] ?? null,
            'harvest_date' => $row['harvest_date'] ?? null,
            'source_harvest' => $row['source_harvest'] ?? null,
            'supplier' => $row['supplier'] ?? null,
            'supplier_uid' => $row['supplier_uid'] ?? null,
            'grower' => $row['grower'] ?? null,
            'vendor' => $row['vendor'] ?? null,
            'farm' => $row['farm'] ?? null,
            'administrative_hold' => $row['administrative_hold'] ?? false,
            'is_tested' => $row['is_tested'] ?? false,
            'lab_name' => $row['lab_name'] ?? null,
            'test_date' => $row['test_date'] ?? null,
            'contaminants_passed' => $row['contaminants_passed'] ?? false,
            'packaged_date' => $row['packaged_date'] ?? null,
            'expiration_date' => $row['expiration_date'] ?? null,
            'is_untaxed' => $row['is_untaxed'] ?? false,
            'is_gls' => $row['is_gls'] ?? false,
            'minimum_price' => $row['minimum_price'] ?? null,
            'weight_threshold' => $row['weight_threshold'] ?? null,
            'description' => $row['description'] ?? null,
            'image' => $row['image'] ?? null,
            'reorder_point' => $row['reorder_point'] ?? null,
            'test_status' => $row['test_status'] ?? null,
            'lab_results' => isset($row['lab_results']) ? (is_string($row['lab_results']) ? $row['lab_results'] : json_encode($row['lab_results'])) : null,
            'batch_notes' => $row['batch_notes'] ?? null,
            'created_at' => $row['created_at'] ?? now(),
            'updated_at' => $row['updated_at'] ?? now(),
        ];
    }
}

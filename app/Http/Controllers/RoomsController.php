<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class RoomsController extends Controller
{
    public function index()
    {
        $rooms = Room::when(\Illuminate\Support\Facades\Schema::hasColumn('rooms','store_id'), function($q){ return $q->where('store_id', \App\Helpers\StoreContext::id()); })->withCount('products')->get();
        return view('rooms.index', compact('rooms'));
    }
    
    public function show($id)
    {
        $room = Room::when(\Illuminate\Support\Facades\Schema::hasColumn('rooms','store_id'), function($q){ return $q->where('store_id', \App\Helpers\StoreContext::id()); })->with('products')->findOrFail($id);
        return view('rooms.show', compact('room'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name',
            'type' => 'required|in:production,storage,processing,sales',
            'max_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $data = [
            'name' => $request->name,
            'type' => $request->type,
            'max_capacity' => $request->max_capacity,
            'current_stock' => 0,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ];
        $data['room_id'] = 'RM-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->name), 0, 4)) . '-' . strtoupper(substr(uniqid(), -4));
        $room = Room::create($data);

        // Mirror to Supabase (best-effort)
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->post(rtrim($supabaseUrl,'/') . '/rest/v1/rooms', [[
                    'name' => $room->name,
                    'room_id' => $room->room_id,
                    'type' => $room->type,
                    'is_active' => $room->is_active,
                    'max_capacity' => $room->max_capacity,
                    'current_stock' => $room->current_stock,
                    'description' => $room->description,
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ]]);
            }
        } catch (\Throwable $e) { /* ignore supabase mirror failures */ }

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name,' . $id,
            'type' => 'required|in:production,storage,processing,sales',
            'max_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $room->update([
            'name' => $request->name,
            'type' => $request->type,
            'max_capacity' => $request->max_capacity,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', $room->is_active),
        ]);

        // Mirror update to Supabase (best-effort)
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/rooms?room_id=eq.' . urlencode($room->room_id), [
                    'name' => $room->name,
                    'type' => $room->type,
                    'is_active' => $room->is_active,
                    'max_capacity' => $room->max_capacity,
                    'description' => $room->description,
                    'updated_at' => now()->toISOString(),
                ]);
            }
        } catch (\Throwable $e) { /* ignore supabase mirror failures */ }

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room
        ]);
    }
    
    public function inventory($id)
    {
        $room = Room::when(\Illuminate\Support\Facades\Schema::hasColumn('rooms','store_id'), function($q){ return $q->where('store_id', \App\Helpers\StoreContext::id()); })->findOrFail($id);
        $products = Product::when(\Illuminate\Support\Facades\Schema::hasColumn('products','store_id'), function($q){ return $q->where('store_id', \App\Helpers\StoreContext::id()); })->where('room', $room->name)->paginate(20);
        
        return response()->json([
            'room' => $room,
            'products' => $products
        ]);
    }
    
    public function transferProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_room' => 'required|string',
            'to_room' => 'required|string|different:from_room',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $product = Product::findOrFail($request->product_id);
        
        if ($product->room !== $request->from_room) {
            return response()->json(['error' => 'Product is not in the specified source room'], 400);
        }
        
        if ($product->quantity < $request->quantity) {
            return response()->json(['error' => 'Insufficient quantity in source room'], 400);
        }
        
        // Update product room
        $product->update(['room' => $request->to_room]);
        
        // In a real application, you would create a transfer record and possibly split the product
        
        return response()->json([
            'message' => "Successfully transferred {$request->quantity} units to {$request->to_room}",
            'product' => $product
        ]);
    }
}

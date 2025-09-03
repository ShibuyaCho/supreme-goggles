<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class RoomsController extends Controller
{
    public function index()
    {
        $rooms = Room::withCount('products')->get();
        return view('rooms.index', compact('rooms'));
    }
    
    public function show($id)
    {
        $room = Room::with('products')->findOrFail($id);
        return view('rooms.show', compact('room'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name',
            'type' => 'required|in:storage,vault,sales_floor,processing,quarantine',
            'capacity' => 'nullable|integer|min:0',
            'temperature_controlled' => 'boolean',
            'security_level' => 'required|in:low,medium,high,maximum',
            'description' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $room = Room::create($request->all());
        
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
            'type' => 'required|in:storage,vault,sales_floor,processing,quarantine',
            'capacity' => 'nullable|integer|min:0',
            'temperature_controlled' => 'boolean',
            'security_level' => 'required|in:low,medium,high,maximum',
            'description' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $room->update($request->all());
        
        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room
        ]);
    }
    
    public function inventory($id)
    {
        $room = Room::findOrFail($id);
        $products = Product::where('room', $room->name)->paginate(20);
        
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

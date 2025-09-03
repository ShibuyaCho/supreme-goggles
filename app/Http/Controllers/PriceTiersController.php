<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PriceTier;
use Illuminate\Support\Facades\Validator;

class PriceTiersController extends Controller
{
    public function index()
    {
        $priceTiers = PriceTier::orderBy('minimum_quantity')->paginate(20);
        return view('price-tiers.index', compact('priceTiers'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'minimum_quantity' => 'required|integer|min:1',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'applicable_categories' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $priceTier = PriceTier::create($request->all());
        
        return response()->json([
            'message' => 'Price tier created successfully',
            'price_tier' => $priceTier
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $priceTier = PriceTier::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'minimum_quantity' => 'required|integer|min:1',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'applicable_categories' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $priceTier->update($request->all());
        
        return response()->json([
            'message' => 'Price tier updated successfully',
            'price_tier' => $priceTier
        ]);
    }
    
    public function destroy($id)
    {
        $priceTier = PriceTier::findOrFail($id);
        $priceTier->delete();
        
        return response()->json(['message' => 'Price tier deleted successfully']);
    }
}

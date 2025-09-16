<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Support\Facades\Http;

class OrderQueueController extends Controller
{
    public function index()
    {
        $pendingOrders = Sale::where('status', 'pending')
                            ->with(['customer', 'saleItems.product'])
                            ->orderBy('created_at', 'asc')
                            ->get();
        
        $preparingOrders = Sale::where('status', 'preparing')
                              ->with(['customer', 'saleItems.product'])
                              ->orderBy('created_at', 'asc')
                              ->get();
        
        $readyOrders = Sale::where('status', 'ready')
                          ->with(['customer', 'saleItems.product'])
                          ->orderBy('created_at', 'asc')
                          ->get();
        
        return view('order-queue.index', compact(
            'pendingOrders',
            'preparingOrders', 
            'readyOrders'
        ));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $order = Sale::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,completed,cancelled'
        ]);
        
        $order->update(['status' => $request->status]);

        // Mirror to Supabase if configured
        try {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_ANON_KEY');
            if ($supabaseUrl && $supabaseKey) {
                Http::withHeaders([
                    'apikey' => $supabaseKey,
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Accept' => 'application/json',
                    'Prefer' => 'return=representation'
                ])->patch(rtrim($supabaseUrl,'/') . '/rest/v1/sales?id=eq.' . urlencode($order->id), [
                    'status' => $request->status,
                    'updated_at' => now()->toISOString(),
                ]);
            }
        } catch (\Throwable $e) { /* best-effort mirror, ignore failures */ }

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }
}

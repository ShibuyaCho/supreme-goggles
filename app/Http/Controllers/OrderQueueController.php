<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Customer;

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
        
        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }
}

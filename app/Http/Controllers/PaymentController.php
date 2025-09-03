<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function index()
    {
        $cart = $this->cartService->getCart();
        return view('payment.index', compact('cart'));
    }
    
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile,gift',
            'customer_id' => 'nullable|exists:customers,id',
            'cash_received' => 'nullable|numeric|min:0',
            'apply_veteran_discount' => 'boolean'
        ]);
        
        $cart = $this->cartService->getCart();
        
        if (empty($cart['items'])) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate totals
            $subtotal = $cart['subtotal'];
            $tax = $cart['tax'];
            $total = $cart['total'];
            
            // Apply veteran discount if applicable
            if ($request->apply_veteran_discount && $request->customer_id) {
                $discount = $total * 0.10; // 10% veteran discount
                $total -= $discount;
            }
            
            // Create sale record
            $sale = Sale::create([
                'transaction_id' => $this->generateTransactionId(),
                'customer_id' => $request->customer_id,
                'employee_id' => auth()->id(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'cash_received' => $request->cash_received,
                'change_given' => $request->payment_method === 'cash' 
                    ? ($request->cash_received - $total) 
                    : 0,
                'status' => 'completed',
                'notes' => $request->notes
            ]);
            
            // Create sale items
            foreach ($cart['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ]);
                
                // Update product inventory
                $product = \App\Models\Product::find($item['id']);
                if ($product) {
                    $product->decrement('quantity', $item['quantity']);
                }
            }
            
            // Clear cart
            $this->cartService->clearCart();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Payment processed successfully',
                'sale' => $sale,
                'receipt_url' => route('sales.receipt', $sale->id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }
    
    private function generateTransactionId()
    {
        return 'TXN-' . now()->format('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
    }
}

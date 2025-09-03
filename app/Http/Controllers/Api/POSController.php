<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Helpers\ToastHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    /**
     * Search customers for POS customer selection
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $customers = Customer::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('medical_card_number', 'LIKE', "%{$query}%");
        })
        ->limit(10)
        ->get()
        ->map(function($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => $customer->customer_type,
                'loyalty_points' => $customer->loyalty_points,
                'medical_card' => $customer->medical_card_number,
                'avatar' => $customer->avatar_url
            ];
        });
        
        return response()->json($customers);
    }
    
    /**
     * Process payment for POS transaction
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:cash,card',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'receipt_options' => 'array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid payment data',
                'details' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Create sale record
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'employee_id' => auth()->id(),
                'total_amount' => $request->total,
                'payment_method' => $request->method,
                'payment_status' => 'completed',
                'sale_date' => now(),
                'receipt_printed' => $request->receipt_options['print'] ?? false,
                'receipt_emailed' => $request->receipt_options['email'] ?? false,
                'receipt_sms' => $request->receipt_options['sms'] ?? false
            ]);
            
            // Add sale items
            foreach ($request->items as $item) {
                $sale->items()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'discount_amount' => $item['discount'] ?? 0
                ]);
                
                // Update product stock
                $product = Product::find($item['id']);
                if ($product) {
                    $product->decrement('stock', $item['quantity']);
                }
            }
            
            // Handle payment method specific data
            if ($request->method === 'cash') {
                $sale->update([
                    'cash_received' => $request->cash_received,
                    'change_given' => $request->change
                ]);
            } elseif ($request->method === 'card') {
                $sale->update([
                    'card_last_four' => $request->card_details['last_four'] ?? null,
                    'card_type' => $request->card_details['type'] ?? null,
                    'transaction_id' => $request->card_details['transaction_id'] ?? null
                ]);
            }
            
            DB::commit();
            
            // Generate receipt URL if needed
            $receiptUrl = null;
            if ($request->receipt_options['print'] ?? false) {
                $receiptUrl = route('sales.receipt', $sale->id);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'sale_id' => $sale->id,
                'receipt_url' => $receiptUrl,
                'transaction_number' => $sale->transaction_number
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Payment processing failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Log age verification attempt
     */
    public function logAgeVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'approved' => 'required|boolean',
            'reason' => 'required|string',
            'employee_id' => 'required',
            'id_type' => 'nullable|string',
            'id_number' => 'nullable|string',
            'birth_date' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid verification data',
                'details' => $validator->errors()
            ], 422);
        }
        
        try {
            // Log to database (you would create an AgeVerification model)
            DB::table('age_verifications')->insert([
                'employee_id' => $request->employee_id,
                'approved' => $request->approved,
                'reason' => $request->reason,
                'id_type' => $request->id_type,
                'id_number_last_four' => $request->id_number ? substr($request->id_number, -4) : null,
                'birth_date' => $request->birth_date,
                'verification_timestamp' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Age verification logged successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to log verification',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get POS configuration and settings
     */
    public function getConfig(Request $request): JsonResponse
    {
        return response()->json([
            'tax_rate' => 0.20, // 20% Oregon tax rate
            'currency' => 'USD',
            'currency_symbol' => '$',
            'oregon_limits' => [
                'flower' => 56.7, // grams
                'concentrates' => 10, // grams
                'edibles' => 454, // grams
                'clones' => 4 // units
            ],
            'age_requirements' => [
                'recreational' => 21,
                'medical' => 18
            ],
            'features' => [
                'metrc_integration' => true,
                'loyalty_program' => true,
                'age_verification' => true,
                'room_transfers' => true,
                'bulk_discounts' => true
            ]
        ]);
    }
    
    /**
     * Get current queue orders
     */
    public function getQueueOrders(Request $request): JsonResponse
    {
        // This would integrate with your order queue system
        return response()->json([
            'orders' => [],
            'total_pending' => 0,
            'total_preparing' => 0,
            'total_ready' => 0
        ]);
    }
}

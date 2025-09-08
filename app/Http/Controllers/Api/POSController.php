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
            'method' => 'required|in:cash,card,debit,credit',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
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
            
            $employeeId = optional(auth()->user()?->employee)->id;
            if (!$employeeId) {
                return response()->json(['error' => 'Employee context not found for user'], 400);
            }

            // Compute subtotal from items and derive tax amount
            $subtotal = 0;
            foreach ($request->items as $it) {
                $line = ($it['price'] * $it['quantity']) - (float)($it['discount'] ?? 0);
                $subtotal += $line;
            }
            $total = (float) $request->total;
            $taxAmount = max(0, $total - $subtotal);

            // Generate sale number
            $saleNumber = 'S' . now()->format('YmdHis') . '-' . random_int(100, 999);

            // Optional customer
            $customer = $request->customer_id ? Customer::find($request->customer_id) : null;

            // Create sale record aligned with schema
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'customer_id' => $customer?->id,
                'employee_id' => $employeeId,
                'customer_type' => $customer?->customer_type ?? 'recreational',
                'customer_info' => $customer ? [
                    'name' => trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')),
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ] : null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $total,
                'payment_method' => $request->method === 'card'
                    ? ((strtolower($request->card_details['type'] ?? '') === 'debit') ? 'debit' : 'credit')
                    : $request->method,
                'payment_reference' => $request->card_details['last_four'] ?? null,
                'amount_paid' => $request->method === 'cash' ? ($request->cash_received ?? $total) : $total,
                'change_given' => $request->method === 'cash' ? ($request->change ?? 0) : 0,
                'status' => 'completed',
                'receipt_printed' => (bool)($request->receipt_options['print'] ?? false),
                'synced_to_metrc' => false,
            ]);
            
            // Add sale items
            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                $sale->saleItems()->create([
                    'product_id' => $item['id'],
                    'product_name' => $product?->name,
                    'product_category' => $product?->category,
                    'product_sku' => $product?->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => ($item['price'] * $item['quantity']) - (float)($item['discount'] ?? 0),
                    'discount_amount' => (float)($item['discount'] ?? 0),
                ]);

                // Update product quantity
                if ($product) {
                    $product->decrement('quantity', $item['quantity']);
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
                'sale_number' => $sale->sale_number,
                'receipt_url' => $receiptUrl,
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

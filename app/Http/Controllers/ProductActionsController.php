<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Room;
use App\Services\MetrcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductActionsController extends Controller
{
    protected $metrcService;

    public function __construct(MetrcService $metrcService)
    {
        $this->metrcService = $metrcService;
    }

    /**
     * Print barcode label for product
     */
    public function printBarcode(Request $request, Product $product)
    {
        try {
            // Log the print action
            Log::info('Barcode printed for product', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barcode label ready for printing',
                'product' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'thc' => $product->thc,
                    'metrc_tag' => $product->metrc_tag
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error printing barcode', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to print barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print exit label for product
     */
    public function printExitLabel(Request $request, Product $product)
    {
        try {
            // Validate product is ready for exit
            if (!$product->metrc_tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product must have METRC tag before printing exit label'
                ], 400);
            }

            // Update METRC status if enabled
            if (config('pos.metrc_enabled', false)) {
                $this->metrcService->updatePackageStatus($product->metrc_tag, 'InTransit');
            }

            // Log the exit label print
            Log::info('Exit label printed for product', [
                'product_id' => $product->id,
                'metrc_tag' => $product->metrc_tag,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exit label ready for printing',
                'product' => [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'metrc_tag' => $product->metrc_tag,
                    'exit_date' => now()->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error printing exit label', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to print exit label: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer product to another room
     */
    public function transferRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'destination_room' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
            'metrc_tag' => 'nullable|string',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            
            // Validate quantity
            if ($request->quantity > $product->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot transfer more than available quantity'
                ], 400);
            }

            $originalRoom = $product->room;
            $originalQuantity = $product->quantity;

            // If transferring partial quantity, create new product record
            if ($request->quantity < $product->quantity) {
                $newProduct = $product->replicate();
                $newProduct->quantity = $request->quantity;
                $newProduct->room = $request->destination_room;
                $newProduct->metrc_tag = $request->metrc_tag ?: $product->metrc_tag . '-SPLIT-' . time();
                $newProduct->save();

                // Update original product quantity
                $product->quantity -= $request->quantity;
                $product->save();

                $transferredProduct = $newProduct;
            } else {
                // Transfer entire product
                $product->room = $request->destination_room;
                if ($request->metrc_tag) {
                    $product->metrc_tag = $request->metrc_tag;
                }
                $product->save();
                $transferredProduct = $product;
            }

            // Log the transfer
            Log::info('Product transferred between rooms', [
                'product_id' => $transferredProduct->id,
                'original_room' => $originalRoom,
                'destination_room' => $request->destination_room,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'user_id' => auth()->id()
            ]);

            // Update METRC if enabled
            if (config('pos.metrc_enabled', false) && $transferredProduct->metrc_tag) {
                $this->metrcService->changePackageLocation(
                    $transferredProduct->metrc_tag,
                    $request->destination_room,
                    $request->notes
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Product transferred successfully',
                'transfer' => [
                    'product_id' => $transferredProduct->id,
                    'from_room' => $originalRoom,
                    'to_room' => $request->destination_room,
                    'quantity' => $request->quantity,
                    'reason' => $request->reason
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error transferring product', [
                'product_id' => $request->product_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get METRC details for product
     */
    public function getMetrcDetails(Product $product)
    {
        try {
            if (!$product->metrc_tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product does not have METRC tag'
                ], 400);
            }

            $metrcData = [];
            
            if (config('pos.metrc_enabled', false)) {
                $metrcData = $this->metrcService->getPackageDetails($product->metrc_tag);
            }

            return response()->json([
                'success' => true,
                'product' => $product,
                'metrc_data' => $metrcData,
                'metrc_url' => "https://api-or.metrc.com/packages/v1/{$product->metrc_tag}"
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching METRC details', [
                'product_id' => $product->id,
                'metrc_tag' => $product->metrc_tag,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch METRC details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product information
     */
    public function updateProduct(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|numeric|min:0',
            'category' => 'sometimes|required|string',
            'thc' => 'nullable|numeric|min:0|max:100',
            'cbd' => 'nullable|numeric|min:0|max:100',
            'weight' => 'nullable|string',
            'room' => 'nullable|string',
            'metrc_tag' => 'nullable|string|unique:products,metrc_tag,' . $product->id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $originalData = $product->toArray();
            
            $product->fill($request->only([
                'name', 'description', 'price', 'quantity', 'category',
                'thc', 'cbd', 'weight', 'room', 'metrc_tag'
            ]));
            
            $product->save();

            // Log the update
            Log::info('Product updated', [
                'product_id' => $product->id,
                'changes' => array_diff_assoc($product->toArray(), $originalData),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct(Product $product)
    {
        try {
            // Check if product is in any active sales
            if ($product->cartItems()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product that is in active carts'
                ], 400);
            }

            $productData = $product->toArray();
            
            // Update METRC if enabled
            if (config('pos.metrc_enabled', false) && $product->metrc_tag) {
                $this->metrcService->finishPackage($product->metrc_tag, 'Destroyed');
            }

            $product->delete();

            // Log the deletion
            Log::info('Product deleted', [
                'product_data' => $productData,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available rooms for transfer
     */
    public function getAvailableRooms()
    {
        try {
            $rooms = [
                'flower-room-1' => 'Flower Room 1',
                'flower-room-2' => 'Flower Room 2',
                'clone-room' => 'Clone Room',
                'drying-room' => 'Drying Room',
                'trim-room' => 'Trim Room',
                'packaging-room' => 'Packaging Room',
                'storage-room' => 'Storage Room',
                'quarantine-room' => 'Quarantine Room'
            ];

            return response()->json([
                'success' => true,
                'rooms' => $rooms
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms: ' . $e->getMessage()
            ], 500);
        }
    }
}

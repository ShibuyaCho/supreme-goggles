<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Deal;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart()
    {
        return Session::get('cart', []);
    }

    public function addToCart(Product $product, $quantity = 1)
    {
        $cart = $this->getCart();
        $productId = $product->id;

        // Check for applicable deals
        $applicableDeals = $this->checkApplicableDeals($product);

        if (isset($cart[$productId])) {
            // Update existing item
            $cart[$productId]['quantity'] += (int) $quantity;

            // Apply BOGO deals if quantity >= 2
            $bogoDeals = collect($applicableDeals)->where('type', 'bogo');
            if (
                $bogoDeals->isNotEmpty()
                && $cart[$productId]['quantity'] >= 2
                && !isset($cart[$productId]['auto_applied_deal'])
            ) {
                $bestBogo = $bogoDeals->first();
                $cart[$productId]['discount'] = (float) $bestBogo->discount_value;
                $cart[$productId]['discount_type'] = 'percentage';
                $cart[$productId]['discount_reason_code'] = 'AUTO-' . $bestBogo->id;
                $cart[$productId]['auto_applied_deal'] = $bestBogo->name;
            }
        } else {
            // Add new item
            $newItem = [
                'id'        => $product->id,
                'name'      => $product->name,
                'price'     => (float) $product->price,
                'category'  => $product->category,
                'weight'    => $product->weight,
                'thc'       => $product->thc,
                'cbd'       => $product->cbd,
                'strain'    => $product->strain,
                'is_untaxed'=> (bool) $product->is_untaxed,
                'is_gls'    => (bool) $product->is_gls,
                'room'      => $product->room,
                'quantity'  => (int) $quantity,
                'discount'  => 0.0,
                'discount_type' => 'percentage',
            ];

            // Auto-apply non-BOGO deals
            $nonBogoDeals = collect($applicableDeals)->where('type', '!=', 'bogo');
            if ($nonBogoDeals->isNotEmpty()) {
                $bestDeal = $nonBogoDeals->sortByDesc('discount_value')->first();
                $newItem  = $this->applyAutomaticDeal($newItem, $bestDeal);
            }

            $cart[$productId] = $newItem;
        }

        Session::put('cart', $cart);
        return $cart;
    }

    public function removeFromCart($productId)
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        Session::put('cart', $cart);
        return $cart;
    }

    public function updateQuantity($productId, $quantity)
    {
        $cart = $this->getCart();

        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            return $this->removeFromCart($productId);
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            Session::put('cart', $cart);
        }

        return $cart;
    }

    public function clearCart()
    {
        Session::forget('cart');
        return [];
    }

    public function setCartItem($productId, $item)
    {
        $cart = $this->getCart();
        $cart[$productId] = $item;
        Session::put('cart', $cart);
        return $cart;
    }

    public function applyItemDiscount($productId, $discountValue, $discountType, $reasonCode)
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['discount'] = (float) $discountValue;
            $cart[$productId]['discount_type'] = $discountType === 'percentage' ? 'percentage' : 'fixed';
            $cart[$productId]['discount_reason_code'] = $reasonCode;
            $cart[$productId]['auto_applied_deal'] = null; // remove auto-applied when manual discount is applied
            Session::put('cart', $cart);
        }

        return $cart;
    }

    public function removeItemDiscount($productId)
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['discount'] = 0.0;
            $cart[$productId]['discount_type'] = 'percentage';
            unset($cart[$productId]['discount_reason_code'], $cart[$productId]['auto_applied_deal']);
            Session::put('cart', $cart);
        }

        return $cart;
    }

    /**
     * Robust totals calculator with zero-division guards and correct proration.
     */
    public function calculateTotals($cart = null, $customerInfo = [])
    {
        $cart = $cart ?? $this->getCart();
        $items = collect($cart);

        // Short-circuit empty cart
        if ($items->isEmpty()) {
            return [
                'subtotal'            => 0.0,
                'taxable_subtotal'    => 0.0,
                'untaxed_subtotal'    => 0.0,
                'discount'            => 0.0,
                'discounted_subtotal' => 0.0,
                'tax'                 => 0.0,
                'total'               => 0.0,
                'tax_rate'            => (float) config('pos.tax_rate', 0.20),
                'is_medical'          => !empty($customerInfo['medical_card']),
                'item_count'          => 0,
            ];
        }

        $taxRate = (float) config('pos.tax_rate', 0.20);
        $taxRate = max(0.0, $taxRate); // no negative tax

        $subtotal = 0.0;
        $taxableSubtotal = 0.0;
        $untaxedSubtotal = 0.0;

        // 1) Compute line totals with item-level discounts applied
        foreach ($items as $item) {
            $qty   = max(0, (int) ($item['quantity'] ?? 0));
            $price = (float) ($item['price'] ?? 0.0);
            $line  = $qty * $price;

            // item discount
            $discVal  = (float) ($item['discount'] ?? 0.0);
            $discType = $item['discount_type'] ?? 'percentage';

            if ($discVal > 0.0) {
                if ($discType === 'percentage') {
                    $line *= (1 - ($discVal / 100.0));
                } else {
                    $line = max(0.0, $line - $discVal);
                }
            }

            $subtotal += $line;

            if (!empty($item['is_untaxed'])) {
                $untaxedSubtotal += $line;
            } else {
                $taxableSubtotal += $line;
            }
        }

        // Guard: if subtotal ended up <= 0 (e.g., free items), stop here
        if ($subtotal <= 0.0) {
            return [
                'subtotal'            => 0.0,
                'taxable_subtotal'    => 0.0,
                'untaxed_subtotal'    => 0.0,
                'discount'            => 0.0,
                'discounted_subtotal' => 0.0,
                'tax'                 => 0.0,
                'total'               => 0.0,
                'tax_rate'            => $taxRate,
                'is_medical'          => !empty($customerInfo['medical_card']),
                'item_count'          => (int) $items->sum('quantity'),
            ];
        }

        // 2) Cart-level discount (exclude GLS items)
        $cartDiscount = Session::get('cart_discount'); // ['type' => 'percentage|fixed', 'value' => float]
        $nonGLSSubtotal = $items->reduce(function ($sum, $item) {
            // Only non-GLS items count towards cart-level discount base
            if (!empty($item['is_gls'])) {
                return $sum;
            }

            $qty   = max(0, (int) ($item['quantity'] ?? 0));
            $price = (float) ($item['price'] ?? 0.0);
            $line  = $qty * $price;

            // replicate item-level discount to get post-item-discount line
            $discVal  = (float) ($item['discount'] ?? 0.0);
            $discType = $item['discount_type'] ?? 'percentage';

            if ($discVal > 0.0) {
                if ($discType === 'percentage') {
                    $line *= (1 - ($discVal / 100.0));
                } else {
                    $line = max(0.0, $line - $discVal);
                }
            }

            return $sum + $line;
        }, 0.0);

        $discountAmount = 0.0;
        if (is_array($cartDiscount)) {
            $val  = max(0.0, (float) ($cartDiscount['value'] ?? 0));
            $type = $cartDiscount['type'] ?? 'fixed';

            if ($type === 'percentage') {
                $discountAmount = $nonGLSSubtotal * ($val / 100.0);
            } else {
                $discountAmount = min($val, $nonGLSSubtotal);
            }

            // clamp: discount cannot exceed subtotal
            $discountAmount = min($discountAmount, $subtotal);
        }

        $discountedSubtotal = max(0.0, $subtotal - $discountAmount);

        // 3) Prorate cart-level discount across taxable & untaxed using proportions.
        // Avoid division by zero.
        $proportionTaxable = $subtotal > 0.0 ? ($taxableSubtotal / $subtotal) : 0.0;
        $discountedTaxableSubtotal = max(
            0.0,
            $taxableSubtotal - ($discountAmount * $proportionTaxable)
        );

        // 4) Medical customers are tax exempt
        $isMedicalCustomer = !empty($customerInfo['medical_card']);
        $tax = $isMedicalCustomer ? 0.0 : ($discountedTaxableSubtotal * $taxRate);

        $total = max(0.0, $discountedSubtotal + $tax);

        return [
            'subtotal'            => round($subtotal, 2),
            'taxable_subtotal'    => round($taxableSubtotal, 2),
            'untaxed_subtotal'    => round($untaxedSubtotal, 2),
            'discount'            => round($discountAmount, 2),
            'discounted_subtotal' => round($discountedSubtotal, 2),
            'tax'                 => round($tax, 2),
            'total'               => round($total, 2),
            'tax_rate'            => $taxRate,
            'is_medical'          => $isMedicalCustomer,
            'item_count'          => (int) $items->sum('quantity'),
        ];
    }

    protected function checkApplicableDeals(Product $product)
    {
        $today = today();
        $dayOfWeek = now()->format('l'); // Monday, Tuesday, etc.
        $selectedLoyaltyCustomer = Session::get('selected_loyalty_customer');

        return Deal::where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function ($query) use ($product, $dayOfWeek, $selectedLoyaltyCustomer) {
                // Skip GLS products for automatic deals
                if ($product->is_gls) {
                    $query->whereRaw('1 = 0'); // No results
                    return;
                }

                // Check frequency
                $query->where(function ($q) use ($dayOfWeek) {
                    $q->where('frequency', 'always')
                      ->orWhere('frequency', 'daily')
                      ->orWhere(function ($weeklyQuery) use ($dayOfWeek) {
                          $weeklyQuery->where('frequency', 'weekly')
                                      ->where('day_of_week', $dayOfWeek);
                      })
                      ->orWhere(function ($monthlyQuery) {
                          $monthlyQuery->where('frequency', 'monthly')
                                       ->where('day_of_month', now()->day);
                      });
                });

                // Check loyalty requirement
                if (!$selectedLoyaltyCustomer) {
                    $query->where('loyalty_only', false);
                }

                // Check category or specific items
                $query->where(function ($itemQuery) use ($product) {
                    $itemQuery->whereJsonContains('categories', $product->category)
                              ->orWhereJsonContains('specific_items', $product->id)
                              ->orWhere('categories', '[]')
                              ->orWhereNull('categories');
                });
            })
            ->get();
    }

    protected function applyAutomaticDeal($item, $deal)
    {
        $item['discount'] = (float) $deal->discount_value;
        $item['discount_type'] = $deal->type === 'percentage' ? 'percentage' : 'fixed';
        $item['discount_reason_code'] = 'AUTO-' . $deal->id;
        $item['auto_applied_deal'] = $deal->name;

        return $item;
    }

    public function getCartSummary()
    {
        $cart = $this->getCart();
        $customerInfo = Session::get('customer_info', []);
        $totals = $this->calculateTotals($cart, $customerInfo);

        return [
            'items'                     => $cart,
            'totals'                    => $totals,
            'cart_discount'             => Session::get('cart_discount'),
            'customer_info'             => $customerInfo,
            'selected_loyalty_customer' => Session::get('selected_loyalty_customer'),
        ];
    }
}

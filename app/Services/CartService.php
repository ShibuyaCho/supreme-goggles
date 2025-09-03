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
            $cart[$productId]['quantity'] += $quantity;
            
            // Apply BOGO deals if quantity >= 2
            $bogoDeals = collect($applicableDeals)->where('type', 'bogo');
            if ($bogoDeals->isNotEmpty() && $cart[$productId]['quantity'] >= 2 && !isset($cart[$productId]['auto_applied_deal'])) {
                $bestBogo = $bogoDeals->first();
                $cart[$productId]['discount'] = $bestBogo->discount_value;
                $cart[$productId]['discount_type'] = 'percentage';
                $cart[$productId]['discount_reason_code'] = 'AUTO-' . $bestBogo->id;
                $cart[$productId]['auto_applied_deal'] = $bestBogo->name;
            }
        } else {
            // Add new item
            $newItem = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'weight' => $product->weight,
                'thc' => $product->thc,
                'cbd' => $product->cbd,
                'strain' => $product->strain,
                'is_untaxed' => $product->is_untaxed,
                'is_gls' => $product->is_gls,
                'room' => $product->room,
                'quantity' => $quantity,
                'discount' => 0,
                'discount_type' => 'percentage'
            ];

            // Auto-apply non-BOGO deals
            $nonBogoDeals = collect($applicableDeals)->where('type', '!=', 'bogo');
            if ($nonBogoDeals->isNotEmpty()) {
                $bestDeal = $nonBogoDeals->sortByDesc('discount_value')->first();
                $newItem = $this->applyAutomaticDeal($newItem, $bestDeal);
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
            $cart[$productId]['discount'] = $discountValue;
            $cart[$productId]['discount_type'] = $discountType;
            $cart[$productId]['discount_reason_code'] = $reasonCode;
            $cart[$productId]['auto_applied_deal'] = null; // Remove auto-applied deal when manual discount is applied
            Session::put('cart', $cart);
        }

        return $cart;
    }

    public function removeItemDiscount($productId)
    {
        $cart = $this->getCart();
        
        if (isset($cart[$productId])) {
            $cart[$productId]['discount'] = 0;
            $cart[$productId]['discount_type'] = 'percentage';
            unset($cart[$productId]['discount_reason_code']);
            unset($cart[$productId]['auto_applied_deal']);
            Session::put('cart', $cart);
        }

        return $cart;
    }

    public function calculateTotals($cart = null, $customerInfo = [])
    {
        $cart = $cart ?? $this->getCart();
        $cartDiscount = Session::get('cart_discount');
        $taxRate = config('pos.tax_rate', 0.20);

        $subtotal = 0;
        $taxableSubtotal = 0;
        $untaxedSubtotal = 0;

        foreach ($cart as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            
            // Apply item discount
            if (isset($item['discount']) && $item['discount'] > 0) {
                if ($item['discount_type'] === 'percentage') {
                    $itemTotal *= (1 - $item['discount'] / 100);
                } else {
                    $itemTotal = max(0, $itemTotal - $item['discount']);
                }
            }

            $subtotal += $itemTotal;

            // Separate taxable and untaxed items
            if ($item['is_untaxed'] ?? false) {
                $untaxedSubtotal += $itemTotal;
            } else {
                $taxableSubtotal += $itemTotal;
            }
        }

        // Apply cart-level discount (only to non-GLS items)
        $discountAmount = 0;
        if ($cartDiscount) {
            $nonGLSSubtotal = collect($cart)->reduce(function ($sum, $item) {
                if ($item['is_gls'] ?? false) {
                    return $sum;
                }
                
                $itemTotal = $item['price'] * $item['quantity'];
                
                if (isset($item['discount']) && $item['discount'] > 0) {
                    if ($item['discount_type'] === 'percentage') {
                        $itemTotal *= (1 - $item['discount'] / 100);
                    } else {
                        $itemTotal = max(0, $itemTotal - $item['discount']);
                    }
                }
                
                return $sum + $itemTotal;
            }, 0);

            if ($cartDiscount['type'] === 'percentage') {
                $discountAmount = $nonGLSSubtotal * ($cartDiscount['value'] / 100);
            } else {
                $discountAmount = min($cartDiscount['value'], $nonGLSSubtotal);
            }
        }

        $discountedSubtotal = $subtotal - $discountAmount;
        $discountedTaxableSubtotal = $taxableSubtotal - ($discountAmount * ($taxableSubtotal / $subtotal));

        // Medical customers are tax exempt
        $isMedicalCustomer = !empty($customerInfo['medical_card']);
        $tax = $isMedicalCustomer ? 0 : $discountedTaxableSubtotal * $taxRate;
        
        $total = $discountedSubtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'taxable_subtotal' => $taxableSubtotal,
            'untaxed_subtotal' => $untaxedSubtotal,
            'discount' => $discountAmount,
            'discounted_subtotal' => $discountedSubtotal,
            'tax' => $tax,
            'total' => $total,
            'tax_rate' => $taxRate,
            'is_medical' => $isMedicalCustomer,
            'item_count' => collect($cart)->sum('quantity')
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
        $item['discount'] = $deal->discount_value;
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
            'items' => $cart,
            'totals' => $totals,
            'cart_discount' => Session::get('cart_discount'),
            'customer_info' => $customerInfo,
            'selected_loyalty_customer' => Session::get('selected_loyalty_customer')
        ];
    }
}

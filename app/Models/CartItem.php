<?php

namespace App\Models;

use Illuminate\Support\Collection;

/**
 * CartItem - Session-based cart item (not stored in database)
 * Used for managing POS cart items before sale completion
 */
class CartItem
{
    public $id;
    public $product_id;
    public $name;
    public $category;
    public $price;
    public $quantity;
    public $weight;
    public $thc;
    public $cbd;
    public $image;
    public $stock;
    public $metrc_tag;
    public $batch_id;
    public $room;
    public $discount_amount = 0;
    public $discount_type = 'fixed';
    public $discount_reason = '';
    public $is_gls = false;
    public $is_untaxed = false;
    public $strain;
    public $supplier;

    public function __construct($product, $quantity = 1)
    {
        if ($product instanceof Product) {
            $this->product_id = $product->id;
            $this->name = $product->name;
            $this->category = $product->category;
            $this->price = $product->price;
            $this->weight = $product->weight;
            $this->thc = $product->thc;
            $this->cbd = $product->cbd;
            $this->image = $product->image;
            $this->stock = $product->stock ?? $product->quantity ?? 0;
            $this->metrc_tag = $product->metrc_tag;
            $this->batch_id = $product->batch_id;
            $this->room = $product->room;
            $this->is_gls = $product->is_gls ?? false;
            $this->is_untaxed = $product->is_untaxed ?? false;
            $this->strain = $product->strain;
            $this->supplier = $product->supplier;
        } else {
            // Handle array input for restored cart items
            foreach ($product as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        $this->quantity = $quantity;
        $this->id = $this->product_id . '_' . time() . '_' . rand(1000, 9999);
    }

    public function getSubtotal()
    {
        return $this->price * $this->quantity;
    }

    public function getTotal()
    {
        return $this->getSubtotal() - $this->discount_amount;
    }

    public function getDiscountPercentage()
    {
        $subtotal = $this->getSubtotal();
        return $subtotal > 0 ? ($this->discount_amount / $subtotal) * 100 : 0;
    }

    public function getTotalWeight()
    {
        if (!$this->weight) {
            return 0;
        }

        $weight = strtolower($this->weight);
        $multiplier = $this->quantity;

        if (str_contains($weight, 'g')) {
            return (float) str_replace(['g', ' '], '', $weight) * $multiplier;
        } elseif (str_contains($weight, 'oz')) {
            return (float) str_replace(['oz', ' '], '', $weight) * 28.3495 * $multiplier;
        } elseif (str_contains($weight, 'mg')) {
            return (float) str_replace(['mg', ' '], '', $weight) / 1000 * $multiplier;
        }

        return 1.0 * $multiplier;
    }

    public function getTotalThc()
    {
        if (in_array($this->category, ['Edibles', 'Tinctures', 'Topicals'])) {
            // For edibles, THC is usually in mg per unit
            return $this->thc * $this->quantity;
        }
        
        // For flower/concentrates, calculate based on weight and percentage
        return ($this->getTotalWeight() * ($this->thc / 100)) * 1000; // Convert to mg
    }

    public function getTotalCbd()
    {
        if (in_array($this->category, ['Edibles', 'Tinctures', 'Topicals'])) {
            return $this->cbd * $this->quantity;
        }
        
        return ($this->getTotalWeight() * ($this->cbd / 100)) * 1000;
    }

    public function applyDiscount($amount, $type = 'fixed', $reason = '')
    {
        $this->discount_type = $type;
        $this->discount_reason = $reason;
        
        if ($type === 'percentage') {
            $this->discount_amount = $this->getSubtotal() * ($amount / 100);
        } else {
            $this->discount_amount = min($amount, $this->getSubtotal());
        }
    }

    public function removeDiscount()
    {
        $this->discount_amount = 0;
        $this->discount_type = 'fixed';
        $this->discount_reason = '';
    }

    public function canAddQuantity($additionalQuantity)
    {
        return ($this->quantity + $additionalQuantity) <= $this->stock;
    }

    public function addQuantity($amount = 1)
    {
        if ($this->canAddQuantity($amount)) {
            $this->quantity += $amount;
            return true;
        }
        return false;
    }

    public function reduceQuantity($amount = 1)
    {
        $this->quantity = max(0, $this->quantity - $amount);
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = max(0, min($quantity, $this->stock));
    }

    public function getOregonLimitCategory()
    {
        return match($this->category) {
            'Flower' => 'flower',
            'Concentrates', 'Extracts' => 'concentrates',
            'Edibles' => 'edibles',
            'Tinctures' => 'liquid_edibles',
            'Topicals' => 'topicals',
            'Pre-Rolls' => str_contains(strtolower($this->name), 'infused') ? 'infused_pre_rolls' : 'flower',
            default => 'other'
        };
    }

    public function isAvailableForSale()
    {
        return $this->stock > 0 && 
               $this->room === 'Sales Floor' && 
               !$this->is_expired;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'weight' => $this->weight,
            'thc' => $this->thc,
            'cbd' => $this->cbd,
            'image' => $this->image,
            'stock' => $this->stock,
            'metrc_tag' => $this->metrc_tag,
            'batch_id' => $this->batch_id,
            'room' => $this->room,
            'discount_amount' => $this->discount_amount,
            'discount_type' => $this->discount_type,
            'discount_reason' => $this->discount_reason,
            'is_gls' => $this->is_gls,
            'is_untaxed' => $this->is_untaxed,
            'strain' => $this->strain,
            'supplier' => $this->supplier,
            'subtotal' => $this->getSubtotal(),
            'total' => $this->getTotal()
        ];
    }

    public static function fromArray($data)
    {
        return new self($data);
    }
}

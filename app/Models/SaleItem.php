<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_category',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'discount_amount',
        'discount_type',
        'discount_reason',
        'tax_amount',
        'cost_per_unit',
        'margin_amount',
        'metrc_tag',
        'batch_id',
        'weight_sold',
        'thc_content',
        'cbd_content',
        'lab_tested',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'weight_sold' => 'decimal:3',
        'thc_content' => 'decimal:2',
        'cbd_content' => 'decimal:2',
        'lab_tested' => 'boolean'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getTotalAttribute()
    {
        return $this->subtotal - $this->discount_amount + $this->tax_amount;
    }

    public function getEffectivePriceAttribute()
    {
        return $this->quantity > 0 ? $this->total / $this->quantity : 0;
    }

    public function getDiscountPercentageAttribute()
    {
        return $this->subtotal > 0 ? ($this->discount_amount / $this->subtotal) * 100 : 0;
    }

    public function getMarginPercentageAttribute()
    {
        return $this->unit_price > 0 ? (($this->unit_price - $this->cost_per_unit) / $this->unit_price) * 100 : 0;
    }

    public function getProfitAttribute()
    {
        return $this->margin_amount * $this->quantity;
    }

    public function getTaxRateAttribute()
    {
        $taxableAmount = $this->subtotal - $this->discount_amount;
        return $taxableAmount > 0 ? ($this->tax_amount / $taxableAmount) * 100 : 0;
    }

    public function getDisplayNameAttribute()
    {
        return $this->product_name ?: ($this->product ? $this->product->name : 'Unknown Product');
    }

    public function getWeightDisplayAttribute()
    {
        if (!$this->weight_sold) {
            return null;
        }

        if ($this->weight_sold >= 28.35) {
            return number_format($this->weight_sold / 28.35, 2) . ' oz';
        }

        return number_format($this->weight_sold, 2) . ' g';
    }

    public function applyDiscount($amount, $type = 'fixed', $reason = '')
    {
        $this->discount_amount = $amount;
        $this->discount_type = $type;
        $this->discount_reason = $reason;
        
        if ($type === 'percentage') {
            $this->discount_amount = $this->subtotal * ($amount / 100);
        }
        
        $this->save();
    }

    public function calculateTax($taxRate)
    {
        $taxableAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $taxableAmount * ($taxRate / 100);
        $this->save();
    }

    public function updateMargin()
    {
        if ($this->product) {
            $this->cost_per_unit = $this->product->cost ?? 0;
            $this->margin_amount = $this->unit_price - $this->cost_per_unit;
            $this->save();
        }
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('product_category', $category);
    }

    public function scopeWithDiscount($query)
    {
        return $query->where('discount_amount', '>', 0);
    }

    public function scopeByMetrcTag($query, $tag)
    {
        return $query->where('metrc_tag', $tag);
    }

    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }
}

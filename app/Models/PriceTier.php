<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'minimum_quantity',
        'discount_percentage',
        'applicable_categories',
        'is_active',
        'description'
    ];

    protected $casts = [
        'minimum_quantity' => 'integer',
        'discount_percentage' => 'decimal:2',
        'applicable_categories' => 'array',
        'is_active' => 'boolean'
    ];

    public function canApplyToCategory($category)
    {
        return empty($this->applicable_categories) || 
               in_array($category, $this->applicable_categories);
    }

    public function calculateDiscount($quantity, $pricePerUnit, $category = null)
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($quantity < $this->minimum_quantity) {
            return 0;
        }

        if ($category && !$this->canApplyToCategory($category)) {
            return 0;
        }

        $totalPrice = $quantity * $pricePerUnit;
        return $totalPrice * ($this->discount_percentage / 100);
    }

    public static function getApplicableTier($quantity, $category = null)
    {
        return self::where('is_active', true)
                  ->where('minimum_quantity', '<=', $quantity)
                  ->where(function($query) use ($category) {
                      if ($category) {
                          $query->whereJsonContains('applicable_categories', $category)
                                ->orWhereNull('applicable_categories');
                      }
                  })
                  ->orderBy('minimum_quantity', 'desc')
                  ->first();
    }
}

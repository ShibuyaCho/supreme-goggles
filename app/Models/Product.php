<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'cost',
        'sku',
        'weight',
        'room',
        'strain',
        'thc',
        'cbd',
        'cbg',
        'cbn',
        'cbc',
        'thc_mg',
        'cbd_mg',
        'cbg_mg',
        'cbn_mg',
        'cbc_mg',
        'supplier',
        'grower',
        'farm',
        'vendor',
        'packaged_date',
        'expiration_date',
        'harvest_date',
        'metrc_tag',
        'batch_id',
        'source_harvest',
        'supplier_uid',
        'is_untaxed',
        'is_gls',
        'minimum_price',
        'description',
        'batch_notes',
        'image',
        'quantity',
        'reorder_point',
        'administrative_hold',
        'test_status',
        'lab_results'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'minimum_price' => 'decimal:2',
        'thc' => 'decimal:1',
        'cbd' => 'decimal:1',
        'cbg' => 'decimal:1',
        'cbn' => 'decimal:1',
        'cbc' => 'decimal:1',
        'thc_mg' => 'decimal:1',
        'cbd_mg' => 'decimal:1',
        'cbg_mg' => 'decimal:1',
        'cbn_mg' => 'decimal:1',
        'cbc_mg' => 'decimal:1',
        'packaged_date' => 'date',
        'expiration_date' => 'date',
        'harvest_date' => 'date',
        'is_untaxed' => 'boolean',
        'is_gls' => 'boolean',
        'quantity' => 'integer',
        'reorder_point' => 'integer',
        'administrative_hold' => 'boolean',
        'lab_results' => 'array'
    ];

    public function getMarginAttribute()
    {
        return $this->price > 0 ? (($this->price - $this->cost) / $this->price) * 100 : 0;
    }

    public function getTotalValueAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getTotalCostAttribute()
    {
        return $this->cost * $this->quantity;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expiration_date) {
            return false;
        }
        
        return $this->expiration_date->isBefore(now()->addDays(30));
    }

    public function getIsLowStockAttribute()
    {
        return $this->quantity <= ($this->reorder_point ?? 10);
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->quantity <= 0;
    }

    public function getWeightInGramsAttribute()
    {
        // Convert weight string to grams for Oregon limits checking
        $weight = strtolower($this->weight);
        
        if (str_contains($weight, 'g')) {
            return (float) str_replace(['g', ' '], '', $weight);
        } elseif (str_contains($weight, 'oz')) {
            return (float) str_replace(['oz', ' '], '', $weight) * 28.3495;
        } elseif (str_contains($weight, 'mg')) {
            return (float) str_replace(['mg', ' '], '', $weight) / 1000;
        } elseif (str_contains($weight, 'ml')) {
            // For liquids, assume 1ml = 1g
            return (float) str_replace(['ml', ' '], '', $weight);
        }
        
        return 1.0; // Default to 1 gram if format not recognized
    }

    public function getThcContentAttribute()
    {
        // For edibles and concentrates, return mg content, otherwise percentage
        if (in_array($this->category, ['Edibles', 'Tinctures', 'Topicals']) && $this->thc_mg) {
            return $this->thc_mg;
        }
        
        return $this->thc ?? 0;
    }

    public function getCbdContentAttribute()
    {
        if (in_array($this->category, ['Edibles', 'Tinctures', 'Topicals']) && $this->cbd_mg) {
            return $this->cbd_mg;
        }
        
        return $this->cbd ?? 0;
    }

    public function isAvailableForSale()
    {
        return !$this->is_out_of_stock &&
               !$this->is_expired &&
               !$this->administrative_hold &&
               in_array($this->test_status, ['passed', 'exempt', null]);
    }

    public function canReduceQuantity($requestedQuantity)
    {
        return $this->quantity >= $requestedQuantity;
    }

    public function reduceQuantity($quantity)
    {
        if (!$this->canReduceQuantity($quantity)) {
            throw new \Exception("Insufficient quantity available");
        }
        
        $this->decrement('quantity', $quantity);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room', 'name');
    }

    public function getTotalSoldAttribute()
    {
        return $this->saleItems()
                   ->whereHas('sale', function($query) {
                       $query->where('status', 'completed');
                   })
                   ->sum('quantity');
    }

    public function getRevenueGeneratedAttribute()
    {
        return $this->saleItems()
                   ->whereHas('sale', function($query) {
                       $query->where('status', 'completed');
                   })
                   ->sum('total');
    }

    public function getPopularityScoreAttribute()
    {
        $totalSold = $this->total_sold;
        $daysSinceCreated = $this->created_at->diffInDays(now());
        
        return $daysSinceCreated > 0 ? $totalSold / $daysSinceCreated : 0;
    }

    public function getOregonLimitCategory()
    {
        // Map product categories to Oregon possession limit categories
        return match($this->category) {
            'Flower' => 'flower',
            'Concentrates', 'Extracts' => 'concentrates',
            'Edibles' => 'edibles',
            'Tinctures' => 'liquid_edibles',
            'Topicals' => 'topicals',
            'Pre-Rolls' => $this->name && str_contains(strtolower($this->name), 'infused') ? 'infused_pre_rolls' : 'flower',
            default => 'other'
        };
    }

    public function needsReorder()
    {
        return $this->is_low_stock || $this->is_out_of_stock;
    }

    public function getReorderQuantity()
    {
        $safetyStock = ($this->reorder_point ?? 10) * 2;
        return max(0, $safetyStock - $this->quantity);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point')
                    ->orWhere(function($q) {
                        $q->whereNull('reorder_point')
                          ->where('quantity', '<=', 10);
                    });
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0)
                    ->where('administrative_hold', false)
                    ->where(function($q) {
                        $q->whereNull('expiration_date')
                          ->orWhere('expiration_date', '>', now());
                    });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByRoom($query, $room)
    {
        return $query->where('room', $room);
    }

    public function scopeTestPassed($query)
    {
        return $query->whereIn('test_status', ['passed', 'exempt']);
    }
}

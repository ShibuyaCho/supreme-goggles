<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SavedSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'employee_id',
        'employee_name',
        'customer_type',
        'customer_info',
        'cart_items',
        'cart_discount',
        'selected_loyalty_customer',
        'total_items',
        'total_amount',
        'notes',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'customer_info' => 'array',
        'cart_items' => 'array',
        'cart_discount' => 'array',
        'selected_loyalty_customer' => 'array',
        'total_items' => 'integer',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Set expiration to 24 hours from creation if not set
            if (!$model->expires_at) {
                $model->expires_at = now()->addDay();
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getExpiresInHoursAttribute()
    {
        return $this->expires_at ? max(0, $this->expires_at->diffInHours(now(), false)) : 0;
    }

    public function getCartItemCountAttribute()
    {
        return is_array($this->cart_items) ? count($this->cart_items) : 0;
    }

    public function getCartSummaryAttribute()
    {
        if (!is_array($this->cart_items)) {
            return 'Empty cart';
        }

        $itemCount = $this->cart_item_count;
        $totalQuantity = collect($this->cart_items)->sum('quantity');
        
        return "{$itemCount} items ({$totalQuantity} total)";
    }

    public function canBeLoaded()
    {
        return !$this->is_expired && $this->status === 'active';
    }

    public function markAsLoaded()
    {
        $this->update(['status' => 'loaded']);
    }

    public function extendExpiration($hours = 24)
    {
        $this->update(['expires_at' => now()->addHours($hours)]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByCustomerType($query, $type)
    {
        return $query->where('customer_type', $type);
    }
}

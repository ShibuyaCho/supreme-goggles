<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'frequency',
        'day_of_week',
        'day_of_month',
        'start_date',
        'end_date',
        'applicable_categories',
        'minimum_purchase',
        'minimum_purchase_type',
        'max_uses',
        'current_uses',
        'email_customers',
        'loyalty_only',
        'medical_only',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'applicable_categories' => 'array',
        'is_active' => 'boolean',
        'email_customers' => 'boolean',
        'loyalty_only' => 'boolean',
        'medical_only' => 'boolean',
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'current_uses' => 'integer',
        'max_uses' => 'integer',
        'day_of_month' => 'integer'
    ];

    public function isActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Check date range
        if ($this->start_date && $now < $this->start_date) {
            return false;
        }

        if ($this->end_date && $now > $this->end_date) {
            return false;
        }

        // Check usage limits
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        // Check frequency
        return $this->isFrequencyValid($now);
    }

    public function isFrequencyValid($date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }

        switch ($this->frequency) {
            case 'always':
                return true;
            case 'daily':
                return true; // Always valid for daily
            case 'weekly':
                return $this->day_of_week ? $date->dayName === $this->day_of_week : true;
            case 'monthly':
                return $this->day_of_month ? $date->day == $this->day_of_month : true;
            default:
                return true;
        }
    }

    public function canApplyToCategory($category)
    {
        return empty($this->applicable_categories) ||
               in_array($category, $this->applicable_categories);
    }

    public function meetsMinimumPurchase($amount, $quantity = null)
    {
        if (!$this->minimum_purchase) {
            return true;
        }

        if ($this->minimum_purchase_type === 'grams') {
            return $quantity ? $quantity >= $this->minimum_purchase : false;
        }

        return $amount >= $this->minimum_purchase;
    }

    public function canApplyToCustomer($customer = null)
    {
        if ($this->loyalty_only && (!$customer || !$customer->loyalty_member_id)) {
            return false;
        }

        if ($this->medical_only && (!$customer || !$customer->is_medical_patient)) {
            return false;
        }

        return true;
    }

    public function calculateDiscount($amount, $category = null, $customer = null, $quantity = null)
    {
        if (!$this->isActive()) {
            return 0;
        }

        if ($category && !$this->canApplyToCategory($category)) {
            return 0;
        }

        if (!$this->canApplyToCustomer($customer)) {
            return 0;
        }

        if (!$this->meetsMinimumPurchase($amount, $quantity)) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                return $amount * ($this->value / 100);
            case 'fixed_amount':
                return min($this->value, $amount);
            case 'bogo':
                // Buy one get one - apply percentage discount
                return $amount * ($this->value / 200); // Half the percentage for BOGO
            case 'bulk':
                return $amount * ($this->value / 100);
            default:
                return 0;
        }
    }

    public function use()
    {
        $this->increment('current_uses');
    }

    public function usageHistory()
    {
        return $this->hasMany(DealUsage::class);
    }

    public function categories()
    {
        // If you have a categories table, you can define a relationship here
        return collect($this->applicable_categories ?? []);
    }

    public function getFormattedDiscountAttribute()
    {
        switch ($this->type) {
            case 'percentage':
                return $this->value . '%';
            case 'fixed_amount':
                return '$' . number_format($this->value, 2);
            case 'bogo':
                return 'BOGO ' . $this->value . '%';
            case 'bulk':
                return $this->value . '% Bulk';
            default:
                return $this->value . '%';
        }
    }

    public function getFrequencyDisplayAttribute()
    {
        switch ($this->frequency) {
            case 'daily':
                return 'Daily';
            case 'weekly':
                return 'Weekly' . ($this->day_of_week ? " ({$this->day_of_week})" : '');
            case 'monthly':
                return 'Monthly' . ($this->day_of_month ? " (Day {$this->day_of_month})" : '');
            case 'always':
                return 'Always Active';
            default:
                return 'Custom';
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', Carbon::today());
                    })
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', Carbon::today());
                    });
    }

    public function scopeLoyaltyOnly($query)
    {
        return $query->where('loyalty_only', true);
    }

    public function scopeMedicalOnly($query)
    {
        return $query->where('medical_only', true);
    }

    public function scopeWithEmailCampaigns($query)
    {
        return $query->where('email_customers', true);
    }
}

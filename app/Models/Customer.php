<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'customer_type',
        'medical_card_number',
        'medical_card_issue_date',
        'medical_card_expiration_date',
        'medical_card_physician',
        'is_patient',
        'is_medical_patient',
        'loyalty_member_id',
        'loyalty_join_date',
        'join_date',
        'loyalty_points',
        'points_earned',
        'points_redeemed',
        'loyalty_tier',
        'tier',
        'is_veteran',
        'is_active',
        'last_visit',
        'total_spent',
        'total_visits',
        'preferred_products',
        'notes',
        'data_retention_consent'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'address' => 'array',
        'medical_card_issue_date' => 'date',
        'medical_card_expiration_date' => 'date',
        'loyalty_join_date' => 'date',
        'join_date' => 'date',
        'is_patient' => 'boolean',
        'is_medical_patient' => 'boolean',
        'is_veteran' => 'boolean',
        'is_active' => 'boolean',
        'last_visit' => 'datetime',
        'total_spent' => 'decimal:2',
        'total_visits' => 'integer',
        'loyalty_points' => 'integer',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'preferred_products' => 'array',
        'data_retention_consent' => 'boolean'
    ];

    public function getFullNameAttribute()
    {
        if ($this->name) {
            return $this->name;
        }
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getNameAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return $this->getFullNameAttribute();
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function isLegalAge()
    {
        return $this->age >= 21;
    }

    public function isMedicalCardValid()
    {
        if ($this->customer_type !== 'medical' || !$this->medical_card_expiration_date) {
            return false;
        }
        
        return $this->medical_card_expiration_date->isFuture();
    }

    public function getVeteranDiscountPercentage()
    {
        return $this->is_veteran ? 10.0 : 0.0;
    }

    public function getLoyaltyPointsValue()
    {
        // Assuming 100 points = $1.00
        return $this->loyalty_points / 100;
    }

    public function canEarnLoyaltyPoints()
    {
        return !empty($this->loyalty_member_id) && $this->is_active;
    }

    public function calculateLoyaltyPoints($saleAmount)
    {
        if (!$this->canEarnLoyaltyPoints()) {
            return 0;
        }
        
        // 1 point per dollar spent
        return (int) floor($saleAmount);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function updateVisitStats()
    {
        $this->last_visit = now();
        $this->total_visits = $this->sales()->where('status', 'completed')->count();
        $this->total_spent = $this->sales()->where('status', 'completed')->sum('total');
        $this->save();
    }

    public function loadPurchaseHistory($limit = 10)
    {
        return $this->sales()
                   ->with('saleItems.product')
                   ->where('status', 'completed')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    public function getPreferredCategories()
    {
        return $this->sales()
                   ->with('saleItems.product')
                   ->where('status', 'completed')
                   ->get()
                   ->flatMap(function($sale) {
                       return $sale->saleItems->pluck('product.category');
                   })
                   ->countBy()
                   ->sortDesc()
                   ->keys()
                   ->take(3)
                   ->toArray();
    }

    public function getAverageOrderValue()
    {
        $completedSales = $this->sales()->where('status', 'completed');
        $totalSales = $completedSales->sum('total');
        $saleCount = $completedSales->count();
        
        return $saleCount > 0 ? $totalSales / $saleCount : 0;
    }

    public function getLastVisitDays()
    {
        return $this->last_visit ? now()->diffInDays($this->last_visit) : null;
    }

    public function isHighValueCustomer($threshold = 1000)
    {
        return $this->total_spent >= $threshold;
    }

    public function getLifetimeValue()
    {
        return $this->total_spent;
    }

    public function getLoyaltyTierProgress()
    {
        $tiers = [
            'Bronze' => 0,
            'Silver' => 500,
            'Gold' => 1500,
            'Platinum' => 3000
        ];
        
        $currentTierThreshold = $tiers[$this->loyalty_tier] ?? 0;
        $nextTier = collect($tiers)->first(function($threshold) {
            return $threshold > $this->total_spent;
        });
        
        if (!$nextTier) {
            return 100; // Already at highest tier
        }
        
        $progress = (($this->total_spent - $currentTierThreshold) / ($nextTier - $currentTierThreshold)) * 100;
        return max(0, min(100, $progress));
    }
}

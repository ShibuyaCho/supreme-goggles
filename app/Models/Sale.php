<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'employee_id',
        'subtotal',
        'tax',
        'total',
        'discount_amount',
        'payment_method',
        'cash_received',
        'change_given',
        'status',
        'void_reason',
        'voided_by',
        'voided_at',
        'loyalty_points_earned',
        'loyalty_points_used',
        'notes',
        'receipt_printed',
        'synced_to_metrc',
        'metrc_sync_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_given' => 'decimal:2',
        'voided_at' => 'datetime',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_used' => 'integer',
        'receipt_printed' => 'boolean',
        'synced_to_metrc' => 'boolean',
        'metrc_sync_date' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function voidedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'voided_by');
    }

    public function getItemCountAttribute()
    {
        return $this->saleItems->sum('quantity');
    }

    public function getIsVoidedAttribute()
    {
        return $this->status === 'voided';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer ? $this->customer->full_name : 'Walk-in Customer';
    }

    public function getEmployeeNameAttribute()
    {
        return $this->employee ? $this->employee->full_name : 'Unknown Employee';
    }

    public function getTaxRateAttribute()
    {
        return $this->subtotal > 0 ? ($this->tax / $this->subtotal) * 100 : 0;
    }

    public function getDiscountPercentageAttribute()
    {
        return $this->subtotal > 0 ? ($this->discount_amount / $this->subtotal) * 100 : 0;
    }

    public function getNetTotalAttribute()
    {
        return $this->total - $this->change_given;
    }

    public function canBeVoided()
    {
        return $this->status === 'completed' && 
               $this->created_at->diffInHours(now()) <= 24; // Can only void within 24 hours
    }

    public function voidSale($reason, $voidedBy)
    {
        if (!$this->canBeVoided()) {
            throw new \Exception('Sale cannot be voided');
        }

        $this->update([
            'status' => 'voided',
            'void_reason' => $reason,
            'voided_by' => $voidedBy,
            'voided_at' => now()
        ]);

        // Restore inventory quantities
        foreach ($this->saleItems as $item) {
            if ($item->product) {
                $item->product->increment('quantity', $item->quantity);
            }
        }

        // Reverse loyalty points if applicable
        if ($this->customer && $this->loyalty_points_earned) {
            $this->customer->decrement('loyalty_points', $this->loyalty_points_earned);
        }
    }

    public function calculateTax($taxRate)
    {
        // Calculate tax based on customer type and product types
        $taxableAmount = $this->subtotal - $this->discount_amount;
        
        // Medical patients may be tax-exempt
        if ($this->customer && $this->customer->customer_type === 'medical') {
            $this->tax = 0;
        } else {
            $this->tax = $taxableAmount * ($taxRate / 100);
        }
        
        $this->total = $taxableAmount + $this->tax;
    }

    public function applyLoyaltyDiscount($pointsToUse)
    {
        if (!$this->customer || !$this->customer->canEarnLoyaltyPoints()) {
            return false;
        }

        $maxPoints = min($pointsToUse, $this->customer->loyalty_points);
        $discountAmount = $maxPoints / 100; // 100 points = $1

        $this->loyalty_points_used = $maxPoints;
        $this->discount_amount += $discountAmount;
        $this->total = max(0, $this->total - $discountAmount);

        return true;
    }

    public function calculateLoyaltyPoints()
    {
        if (!$this->customer || !$this->customer->canEarnLoyaltyPoints()) {
            return 0;
        }

        // 1 point per dollar spent (after tax)
        $this->loyalty_points_earned = (int) floor($this->total);
        return $this->loyalty_points_earned;
    }

    public function markAsPrinted()
    {
        $this->update(['receipt_printed' => true]);
    }

    public function needsMetrcSync()
    {
        return $this->status === 'completed' && !$this->synced_to_metrc;
    }

    public function markAsSyncedToMetrc()
    {
        $this->update([
            'synced_to_metrc' => true,
            'metrc_sync_date' => now()
        ]);
    }

    public function getReceiptData()
    {
        return [
            'transaction_id' => $this->transaction_id,
            'date' => $this->created_at->format('M j, Y'),
            'time' => $this->created_at->format('g:i A'),
            'employee' => $this->employee_name,
            'customer' => $this->customer_name,
            'items' => $this->saleItems->map(function($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total
                ];
            }),
            'subtotal' => $this->subtotal,
            'discount' => $this->discount_amount,
            'tax' => $this->tax,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'cash_received' => $this->cash_received,
            'change_given' => $this->change_given,
            'loyalty_points_earned' => $this->loyalty_points_earned,
            'loyalty_points_used' => $this->loyalty_points_used
        ];
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeNeedsMetrcSync($query)
    {
        return $query->where('status', 'completed')
                    ->where('synced_to_metrc', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}

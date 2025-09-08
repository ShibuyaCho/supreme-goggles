<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'customer_id',
        'employee_id',
        'customer_type',
        'customer_info',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'amount_paid',
        'change_given',
        'status',
        'void_reason',
        'voided_by',
        'voided_at',
        'loyalty_points_earned',
        'loyalty_points_used',
        'cart_items',
        'applied_deals',
        'tax_rate',
        'notes',
        'receipt_printed',
        'synced_to_metrc',
        'metrc_sync_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'voided_at' => 'datetime',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_used' => 'integer',
        'receipt_printed' => 'boolean',
        'synced_to_metrc' => 'boolean',
        'metrc_sync_date' => 'datetime',
        'customer_info' => 'array',
        'cart_items' => 'array',
        'applied_deals' => 'array',
        'tax_rate' => 'decimal:4',
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

    public function getComputedTaxRateAttribute()
    {
        $base = max(0, (float)$this->subtotal - (float)$this->discount_amount);
        return $base > 0 ? ((float)$this->tax_amount / $base) * 100 : 0.0;
    }

    public function getDiscountPercentageAttribute()
    {
        return $this->subtotal > 0 ? ((float)$this->discount_amount / (float)$this->subtotal) * 100 : 0.0;
    }

    public function getNetTotalAttribute()
    {
        return (float)$this->total_amount - (float)($this->change_given ?? 0);
    }

    public function canBeVoided()
    {
        return $this->status === 'completed' && $this->created_at->diffInHours(now()) <= 24;
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
            'voided_at' => now(),
        ]);

        foreach ($this->saleItems as $item) {
            if ($item->product) {
                $item->product->increment('quantity', $item->quantity);
            }
        }

        if ($this->customer && $this->loyalty_points_earned) {
            $this->customer->decrement('loyalty_points', $this->loyalty_points_earned);
        }
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
            'metrc_sync_date' => now(),
        ]);
    }

    public function getReceiptData()
    {
        return [
            'transaction_id' => $this->sale_number,
            'date' => $this->created_at->format('M j, Y'),
            'time' => $this->created_at->format('g:i A'),
            'employee' => $this->employee_name,
            'customer' => $this->customer_name,
            'items' => $this->saleItems->map(function ($item) {
                return [
                    'name' => $item->product->name ?? $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->total_price,
                ];
            }),
            'subtotal' => $this->subtotal,
            'discount' => $this->discount_amount,
            'tax' => $this->tax_amount,
            'total' => $this->total_amount,
            'payment_method' => $this->payment_method,
            'cash_received' => $this->amount_paid,
            'change_given' => $this->change_given,
            'loyalty_points_earned' => $this->loyalty_points_earned,
            'loyalty_points_used' => $this->loyalty_points_used,
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
        return $query->where('status', 'completed')->where('synced_to_metrc', false);
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

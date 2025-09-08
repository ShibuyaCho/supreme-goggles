<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'pin',
        'password',
        'department',
        'position',
        'role',
        'hire_date',
        'hourly_rate',
        'is_active',
        'status',
        'permissions',
        'last_login',
        'notes',
        'worker_permit',
        'metrc_api_key'
    ];

    protected $hidden = [
        'password',
        'pin'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'last_login' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'permissions' => 'array'
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isActive()
    {
        if (array_key_exists('is_active', $this->attributes)) {
            return (bool) ($this->attributes['is_active'] ?? false);
        }
        return ($this->attributes['status'] ?? 'active') === 'active';
    }

    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function clockEntries()
    {
        return $this->hasMany(TimeClockEntry::class);
    }

    public function getRoleAttribute()
    {
        // Prefer stored role column when present
        if (array_key_exists('role', $this->attributes) && !empty($this->attributes['role'])) {
            return strtolower((string) $this->attributes['role']);
        }
        // Fallback: infer from position when role is not stored
        $p = strtolower(trim($this->position ?? ''));
        if ($p === 'admin' || $p === 'administrator') return 'admin';
        if ($p === 'manager' || $p === 'general manager' || $p === 'assistant manager') return 'manager';
        if ($p === 'inventory' || $p === 'inventory manager' || $p === 'inventory specialist') return 'inventory';
        if ($p === 'budtender') return 'budtender';
        if ($p === 'cashier' || $p === 'sales' || $p === 'sales associate') return 'cashier';
        return 'cashier';
    }

    public function getTotalSalesAttribute()
    {
        return $this->sales()->where('status', 'completed')->sum('total');
    }

    public function getTotalTransactionsAttribute()
    {
        return $this->sales()->where('status', 'completed')->count();
    }

    public function getAverageOrderValueAttribute()
    {
        $transactions = $this->total_transactions;
        return $transactions > 0 ? $this->total_sales / $transactions : 0;
    }

    public function canVoidSales()
    {
        return $this->hasPermission('void_sales') || $this->hasPermission('admin');
    }

    public function canManageInventory()
    {
        return $this->hasPermission('manage_inventory') || $this->hasPermission('admin');
    }

    public function canAccessReports()
    {
        return $this->hasPermission('view_reports') || $this->hasPermission('admin');
    }

    public function canManageCustomers()
    {
        return $this->hasPermission('manage_customers') || $this->hasPermission('admin');
    }

    public function canManageEmployees()
    {
        return $this->hasPermission('manage_employees') || $this->hasPermission('admin');
    }

    public function getStatusAttribute()
    {
        // Normalize status consumers to work with is_active boolean
        if (array_key_exists('is_active', $this->attributes)) {
            return ((bool) ($this->attributes['is_active'] ?? false)) ? 'active' : 'inactive';
        }
        return $this->attributes['status'] ?? 'active';
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = strtolower(trim((string) $value));
    }

    public function getWeeklyHours($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->startOfWeek();
        $endDate = $endDate ?: now()->endOfWeek();
        
        return $this->clockEntries()
            ->whereBetween('clock_in', [$startDate, $endDate])
            ->whereNotNull('clock_out')
            ->get()
            ->sum(function ($entry) {
                return $entry->clock_in->diffInHours($entry->clock_out);
            });
    }
}

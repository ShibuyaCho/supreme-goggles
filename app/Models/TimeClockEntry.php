<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeClockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'clock_in',
        'clock_out',
        'notes',
        'adjusted_by',
        'adjusted_at',
        'adjustments',
        'source',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'adjusted_at' => 'datetime',
        'adjustments' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function getDurationSecondsAttribute(): int
    {
        if (!$this->clock_in || !$this->clock_out) return 0;
        return $this->clock_out->diffInSeconds($this->clock_in);
    }
}

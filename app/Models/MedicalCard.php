<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCard extends Model
{
    protected $fillable = [
        'customer_id', 'card_number', 'is_patient', 'expires_at',
    ];

    protected $casts = [
        'is_patient' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}

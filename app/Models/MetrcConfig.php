<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetrcConfig extends Model
{
    protected $fillable = [
        'environment', 'base_url', 'integrator_key', 'user_key',
        'facility_license', 'is_active', 'enabled_live_sync',
        'integrator_key_last4', 'user_key_last4',
        'last_tested_at', 'last_test_status',
    ];

    protected $casts = [
        'integrator_key'    => 'encrypted',
        'user_key'          => 'encrypted',
        'is_active'         => 'boolean',
        'enabled_live_sync' => 'boolean',
        'last_tested_at'    => 'datetime',
    ];

    /** Keep *_last4 in sync when setting plaintext keys */
    public function setIntegratorKeyAttribute($value): void
    {
        $this->attributes['integrator_key'] = $value;
        $this->attributes['integrator_key_last4'] = is_string($value) && strlen($value) >= 4
            ? substr($value, -4)
            : null;
    }

    public function setUserKeyAttribute($value): void
    {
        $this->attributes['user_key'] = $value;
        $this->attributes['user_key_last4'] = is_string($value) && strlen($value) >= 4
            ? substr($value, -4)
            : null;
    }

    /** Scope for active config, optional env filter */
    public function scopeActive($query, ?string $environment = null)
    {
        $query->where('is_active', true);
        if ($environment !== null) {
            $query->where('environment', $environment);
        }
        return $query;
    }
}

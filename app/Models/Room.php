<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'capacity',
        'temperature_controlled',
        'security_level',
        'description',
        'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'temperature_controlled' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'room', 'name');
    }

    public function getCurrentCapacity()
    {
        return $this->products()->sum('quantity');
    }

    public function getAvailableCapacity()
    {
        return $this->capacity ? $this->capacity - $this->getCurrentCapacity() : null;
    }

    public function isOverCapacity()
    {
        return $this->capacity && $this->getCurrentCapacity() > $this->capacity;
    }

    public function canAccommodate($quantity)
    {
        if (!$this->capacity) {
            return true; // No capacity limit
        }
        
        return $this->getAvailableCapacity() >= $quantity;
    }

    public function getSecurityLevelColor()
    {
        return match($this->security_level) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'maximum' => 'red',
            default => 'gray'
        };
    }

    public function getTypeDisplayName()
    {
        return match($this->type) {
            'storage' => 'Storage Room',
            'vault' => 'Secure Vault',
            'sales_floor' => 'Sales Floor',
            'processing' => 'Processing Lab',
            'quarantine' => 'Quarantine Room',
            default => 'Unknown'
        };
    }
}

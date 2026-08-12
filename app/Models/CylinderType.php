<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CylinderType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'price_premium',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'price_premium' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

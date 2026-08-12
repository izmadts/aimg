<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'gas_product_id',
        'quantity',
        'price',
        'total',
        'cylinder_id',
        'cylinder_action',
        'cylinder_sale_price',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'grade',
        'notes'
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'cylinder_sale_price' => 'decimal:2',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function gasProduct()
    {
        return $this->belongsTo(GasProduct::class);
    }

    public function cylinder()
    {
        return $this->belongsTo(Cylinder::class);
    }

    // Accessors
    public function getCylinderActionLabelAttribute()
    {
        $labels = [
            'none' => 'No Action',
            'issued' => 'Issued',
            'sold' => 'Sold',
            'returned' => 'Returned'
        ];
        return $labels[$this->cylinder_action] ?? ucfirst($this->cylinder_action);
    }

    // Mutator
    public function setCylinderActionAttribute($value)
    {
        $map = [
            'none' => 'none',
            'issue' => 'issued',
            'sell' => 'sold',
            'return' => 'returned',
            'issued' => 'issued',
            'sold' => 'sold',
            'returned' => 'returned'
        ];
        $this->attributes['cylinder_action'] = $map[$value] ?? 'none';
    }
}
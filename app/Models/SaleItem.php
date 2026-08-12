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
        'gas_quantity',
        'gas_price',
        'gas_total',
        'cylinder_id',
        'cylinder_action',
        'cylinder_quantity',
        'cylinder_unit_price',
        'cylinder_total',
        'is_customer_cylinder',
        'notes'
    ];

    protected $casts = [
        'gas_quantity' => 'decimal:2',
        'gas_price' => 'decimal:2',
        'gas_total' => 'decimal:2',
        'cylinder_quantity' => 'integer',
        'cylinder_unit_price' => 'decimal:2',
        'cylinder_total' => 'decimal:2',
        'is_customer_cylinder' => 'boolean',
    ];

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

    public function getHasGasAttribute()
    {
        return !is_null($this->gas_product_id);
    }

    public function getHasCylinderAttribute()
    {
        return !is_null($this->cylinder_id);
    }

    public function getLineTotalAttribute()
    {
        return $this->gas_total + $this->cylinder_total;
    }

    public function getCylinderActionLabelAttribute()
    {
        $labels = [
            'issue' => 'Issued (deposit)',
            'sell' => 'Sold',
        ];
        return $labels[$this->cylinder_action] ?? '—';
    }
}

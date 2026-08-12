<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'gas_product_id',
        'gas_quantity',
        'gas_price',
        'gas_total',
        'cylinder_id',
        'cylinder_action',
        'cylinder_quantity',
        'cylinder_unit_price',
        'cylinder_total',
        'notes'
    ];

    protected $casts = [
        'gas_quantity' => 'decimal:2',
        'gas_price' => 'decimal:2',
        'gas_total' => 'decimal:2',
        'cylinder_quantity' => 'integer',
        'cylinder_unit_price' => 'decimal:2',
        'cylinder_total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
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
            'purchase' => 'Purchased (new/empty)',
            'exchange' => 'Exchanged (empty for filled)',
            'return_to_supplier' => 'Returned to supplier',
        ];
        return $labels[$this->cylinder_action] ?? '—';
    }
}

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
        'quantity',
        'price',
        'total',
        'cylinder_id',
        'cylinder_action',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'notes'
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date'
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

    // Update gas stock when purchase is confirmed
    public function updateGasStock()
    {
        $gasProduct = $this->gasProduct;
        $gasProduct->increment('current_stock', $this->quantity);
    }
}
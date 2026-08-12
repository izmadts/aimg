<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCylinderTransaction extends Model
{
    use HasFactory;

    protected $table = 'supplier_cylinder_transactions';

    protected $fillable = [
        'supplier_id',
        'cylinder_id',
        'purchase_id',
        'user_id',
        'transaction_type',
        'transaction_date',
        'gas_quantity_at_transaction',
        'deposit_adjustment',
        'cylinder_condition',
        'damage_charge',
        'reference_document',
        'remarks'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'gas_quantity_at_transaction' => 'decimal:2',
        'deposit_adjustment' => 'decimal:2',
        'damage_charge' => 'decimal:2'
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cylinder()
    {
        return $this->belongsTo(Cylinder::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByCylinder($query, $cylinderId)
    {
        return $query->where('cylinder_id', $cylinderId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getTypeLabelAttribute()
    {
        $labels = [
            'received_filled' => 'Received Filled Cylinder',
            'received_empty' => 'Received Empty Cylinder',
            'returned_empty' => 'Returned Empty Cylinder',
            'purchased_new' => 'Purchased New Cylinder',
            'exchanged' => 'Exchanged Cylinder'
        ];
        return $labels[$this->transaction_type] ?? ucfirst(str_replace('_', ' ', $this->transaction_type));
    }

    public function getConditionLabelAttribute()
    {
        $labels = [
            'good' => 'Good',
            'damaged' => 'Damaged',
            'expired' => 'Expired',
            'under_maintenance' => 'Under Maintenance'
        ];
        return $labels[$this->cylinder_condition] ?? $this->cylinder_condition;
    }

    public function getConditionColorAttribute()
    {
        $colors = [
            'good' => 'green',
            'damaged' => 'red',
            'expired' => 'orange',
            'under_maintenance' => 'yellow'
        ];
        return $colors[$this->cylinder_condition] ?? 'gray';
    }
}
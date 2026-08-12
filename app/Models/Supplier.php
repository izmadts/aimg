<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'erp_supplier_id',
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'ntn_number',
        'cnic',
        'contact_person',
        'contact_person_phone',
        'opening_balance',
        'balance_type',
        'cylinder_deposit_paid',
        'cylinder_return_days',
        'is_active',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'cylinder_deposit_paid' => 'decimal:2',
        'cylinder_return_days' => 'integer'
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function cylinders()
    {
        return $this->hasMany(Cylinder::class);
    }

    public function cylinderTransactions()
    {
        return $this->hasMany(SupplierCylinderTransaction::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithPendingPayments($query)
    {
        return $query->whereHas('purchases', function ($q) {
            $q->where('payment_status', '!=', 'paid')
              ->where('status', '!=', 'cancelled');
        });
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getTotalPayableAttribute()
    {
        return $this->purchases()
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum('balance_due');
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->purchases()
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
    }

    public function getTotalCylindersAttribute()
    {
        return $this->cylinders()->count();
    }

    public function getCylindersWithSupplierAttribute()
    {
        return $this->cylinders()
            ->where('status', 'in_house_empty')
            ->whereNotNull('supplier_id')
            ->get();
    }

    public function getBalanceLabelAttribute()
    {
        $labels = [
            'payable' => 'Payable to Supplier',
            'receivable' => 'Receivable from Supplier'
        ];
        return $labels[$this->balance_type] ?? $this->balance_type;
    }

    // ============================================
    // METHODS
    // ============================================

    public function updateBalance($amount, $type = 'add')
    {
        if ($type === 'add') {
            $this->increment('opening_balance', $amount);
        } else {
            $this->decrement('opening_balance', $amount);
        }
        return $this;
    }

    public function getFormattedBalanceAttribute()
    {
        $sign = $this->balance_type === 'payable' ? '-' : '';
        return $sign . 'Rs. ' . number_format($this->opening_balance, 2);
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && empty($model->updated_by)) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'erp_customer_id',
        'name',
        'phone',
        'email',
        'address',
        'ntn_number',
        'cnic',
        'security_deposit',
        'opening_balance',
        'is_active',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'security_deposit' => 'decimal:2',
        'opening_balance' => 'decimal:2'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function cylinderIssues()
    {
        return $this->hasMany(CylinderIssuedDetail::class);
    }

    public function activeCylinderIssues()
    {
        return $this->hasMany(CylinderIssuedDetail::class)->where('status', 'issued');
    }

    public function cylinderTransactions()
    {
        return $this->hasMany(CylinderTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithIssuedCylinders($query)
    {
        return $query->whereHas('activeCylinderIssues');
    }

    // Accessors
    public function getIssuedCylindersAttribute()
    {
        return $this->activeCylinderIssues()->with('cylinder')->get();
    }

    public function getTotalIssuedCylindersAttribute()
    {
        return $this->activeCylinderIssues()->sum('quantity');
    }

    public function getTotalSalesAmountAttribute()
    {
        return $this->sales()->where('status', '!=', 'cancelled')->sum('grand_total');
    }

    public function getPendingBalanceAttribute()
    {
        return $this->sales()->where('payment_status', '!=', 'paid')->sum('balance_due')
            + $this->opening_balance;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->customer_code)) {
                $last = self::orderBy('id', 'desc')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $model->customer_code = 'CUST-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            }

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
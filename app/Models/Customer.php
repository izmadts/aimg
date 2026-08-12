<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'erp_customer_id',
        'name',
        'phone',
        'email',
        'address',
        'ntn_number',
        'cnic',
        'security_deposit',
        'is_active',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'security_deposit' => 'decimal:2'
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

    public function cylinders()
    {
        return $this->hasMany(Cylinder::class, 'current_customer_id');
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
        return $query->whereHas('cylinders', function ($q) {
            $q->where('status', 'issued');
        });
    }

    // Accessors
    public function getIssuedCylindersAttribute()
    {
        return $this->cylinders()->where('status', 'issued')->get();
    }

    public function getTotalIssuedCylindersAttribute()
    {
        return $this->cylinders()->where('status', 'issued')->count();
    }

    public function getTotalSalesAmountAttribute()
    {
        return $this->sales()->where('status', '!=', 'cancelled')->sum('grand_total');
    }

    public function getPendingBalanceAttribute()
    {
        return $this->sales()->where('payment_status', '!=', 'paid')->sum('balance_due');
    }

    // Boot method
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
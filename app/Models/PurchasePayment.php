<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'amount',
        'payment_method',
        'payment_date',
        'transaction_no',
        'notes',
        'created_by',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date'
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'cash' => '💵 Cash',
            'bank_transfer' => '🏦 Bank Transfer',
            'cheque' => '📝 Cheque',
            'online' => '💳 Online Payment'
        ];
        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => '⏳ Pending',
            'completed' => '✅ Completed',
            'failed' => '❌ Failed'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // ============================================
    // METHODS
    // ============================================

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
        return $this;
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
        return $this;
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->transaction_no)) {
                $model->transaction_no = 'PAY-' . time() . '-' . rand(100, 999);
            }
            if (empty($model->status)) {
                $model->status = 'completed';
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}
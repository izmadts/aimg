<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'transaction_type',
        'account_id',
        'opposite_account_id',
        'date',
        'debit',
        'credit',
        'description',
        'reference_no',
        'reference_type',
        'reference_id',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'approved_at' => 'datetime'
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function oppositeAccount()
    {
        return $this->belongsTo(Account::class, 'opposite_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Polymorphic relationships
    public function reference()
    {
        return $this->morphTo();
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeIncome($query)
    {
        return $query->where('transaction_type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', 'expense');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getTypeLabelAttribute()
    {
        $labels = [
            'income' => 'Income',
            'expense' => 'Expense',
            'transfer' => 'Transfer',
            'journal' => 'Journal Entry',
            'sale' => 'Sale',
            'purchase' => 'Purchase',
            'payment' => 'Payment',
            'receipt' => 'Receipt'
        ];
        return $labels[$this->transaction_type] ?? ucfirst($this->transaction_type);
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            'income' => 'green',
            'expense' => 'red',
            'transfer' => 'blue',
            'journal' => 'purple',
            'sale' => 'green',
            'purchase' => 'orange',
            'payment' => 'red',
            'receipt' => 'green'
        ];
        return $colors[$this->transaction_type] ?? 'gray';
    }

    // ✅ AMOUNT ACCESSOR - Returns debit or credit whichever is greater
    public function getAmountAttribute()
    {
        return $this->debit ?: $this->credit;
    }

    public function getIsDebitAttribute()
    {
        return $this->debit > 0;
    }

    public function getIsCreditAttribute()
    {
        return $this->credit > 0;
    }

    // ============================================
    // METHODS
    // ============================================

    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        return $this;
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason ?? 'Rejected'
        ]);
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
                $model->transaction_no = self::generateTransactionNo($model->transaction_type);
            }
            if (empty($model->status)) {
                $model->status = 'pending';
            }
        });
    }

    public static function generateTransactionNo($type)
    {
        $prefix = [
            'income' => 'INC',
            'expense' => 'EXP',
            'transfer' => 'TRF',
            'journal' => 'JRN',
            'sale' => 'SAL',
            'purchase' => 'PUR',
            'payment' => 'PAY',
            'receipt' => 'REC'
        ];

        $prefixCode = $prefix[$type] ?? 'TRX';
        $lastTransaction = self::where('transaction_no', 'LIKE', $prefixCode . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefixCode . '-' . date('Ymd') . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
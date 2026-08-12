<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_no',
        'date',
        'description',
        'transaction_type',
        'reference_type',
        'reference_id',
        'account_id',
        'opposite_account_id',
        'debit',
        'credit',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId)
            ->orWhere('opposite_account_id', $accountId);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getTypeLabelAttribute()
    {
        $labels = [
            'sale' => 'Sale',
            'purchase' => 'Purchase',
            'expense' => 'Expense',
            'income' => 'Income',
            'deposit' => 'Cylinder Deposit',
            'refund' => 'Deposit Refund',
            'salary' => 'Salary',
            'advance' => 'Employee Advance',
            'damage' => 'Damage Charge',
            'payment' => 'Payment'
        ];
        return $labels[$this->transaction_type] ?? ucfirst($this->transaction_type);
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            'sale' => 'green',
            'purchase' => 'blue',
            'expense' => 'red',
            'income' => 'green',
            'deposit' => 'yellow',
            'refund' => 'orange',
            'salary' => 'purple',
            'advance' => 'blue',
            'damage' => 'red',
            'payment' => 'green'
        ];
        return $colors[$this->transaction_type] ?? 'gray';
    }

    public function getAmountAttribute()
    {
        return $this->debit ?: $this->credit;
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
            if (empty($model->entry_no)) {
                $model->entry_no = self::generateEntryNo();
            }
            if (empty($model->status)) {
                $model->status = 'approved';
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    public static function generateEntryNo()
    {
        $prefix = 'ACC-' . date('Ymd') . '-';
        $last = self::where('entry_no', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($last) {
            $lastNumber = intval(substr($last->entry_no, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }
}
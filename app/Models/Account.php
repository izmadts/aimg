<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'parent_id',
        'opening_balance',
        'current_balance',
        'is_active',
        'description',
        'created_by'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ✅ Add this relationship
    public function entries()
    {
        return $this->hasMany(AccountingEntry::class);
    }

    // ✅ Add alias for transactions (if needed)
    public function transactions()
    {
        return $this->hasMany(AccountingEntry::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeIncome($query)
    {
        return $query->where('account_type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('account_type', 'expense');
    }

    public function scopeAsset($query)
    {
        return $query->where('account_type', 'asset');
    }

    public function scopeLiability($query)
    {
        return $query->where('account_type', 'liability');
    }

    public function scopeEquity($query)
    {
        return $query->where('account_type', 'equity');
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getTypeLabelAttribute()
    {
        $labels = [
            'asset' => 'Asset (Jis mein paisa hai)',
            'liability' => 'Liability (Jis mein dena hai)',
            'income' => 'Income (Aamdani)',
            'expense' => 'Expense (Kharcha)',
            'equity' => 'Equity (Malik ka hissa)'
        ];
        return $labels[$this->account_type] ?? ucfirst($this->account_type);
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            'asset' => 'blue',
            'liability' => 'red',
            'income' => 'green',
            'expense' => 'orange',
            'equity' => 'purple'
        ];
        return $colors[$this->account_type] ?? 'gray';
    }

    public function getBalanceAttribute()
    {
        $totalDebit = $this->entries()->where('status', 'approved')->sum('debit');
        $totalCredit = $this->entries()->where('status', 'approved')->sum('credit');
        
        $balance = $totalDebit - $totalCredit;
        
        if (in_array($this->account_type, ['liability', 'income', 'equity'])) {
            $balance = -$balance;
        }
        
        return $balance + $this->opening_balance;
    }

    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->account_name . ' > ' . $this->account_name;
        }
        return $this->account_name;
    }

    // ============================================
    // METHODS
    // ============================================

    public function updateBalance()
    {
        $this->current_balance = $this->balance;
        $this->save();
        return $this;
    }

    public function isParent()
    {
        return $this->children()->count() > 0;
    }

    public function hasChildren()
    {
        return $this->children()->exists();
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->account_code)) {
                $model->account_code = $model->generateAccountCode();
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    private function generateAccountCode()
    {
        $prefix = [
            'asset' => '1',
            'liability' => '2',
            'equity' => '3',
            'income' => '4',
            'expense' => '5'
        ];

        $prefixCode = $prefix[$this->account_type] ?? '9';
        
        $lastAccount = self::where('account_code', 'LIKE', $prefixCode . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAccount) {
            $lastNumber = intval(substr($lastAccount->account_code, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefixCode . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
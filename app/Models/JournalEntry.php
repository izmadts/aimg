<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JournalEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entry_no',
        'date',
        'description',
        'sale_id',
        'purchase_id',
        'type',
        'total_debit',
        'total_credit',
        'entries',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
        'reference_no'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'entries' => 'array',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the sale associated with this journal entry.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the purchase associated with this journal entry.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the user who created this journal entry.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this journal entry.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include sale entries.
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    /**
     * Scope a query to only include purchase entries.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    /**
     * Scope a query to only include expense entries.
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to only include deposit entries.
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope a query to only include refund entries.
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

    /**
     * Scope a query to only include approved entries.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending entries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by current month.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    /**
     * Scope a query to filter by current year.
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('date', now()->year);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Get the total amount.
     */
    public function getTotalAmountAttribute()
    {
        return $this->total_debit > $this->total_credit 
            ? $this->total_debit 
            : $this->total_credit;
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'sale' => 'Sale Invoice',
            'purchase' => 'Purchase Invoice',
            'expense' => 'Expense',
            'deposit' => 'Cylinder Deposit',
            'refund' => 'Refund'
        ];
        
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'draft' => 'gray'
        ];
        
        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get formatted entry details for display.
     */
    public function getFormattedEntriesAttribute()
    {
        if (empty($this->entries) || !is_array($this->entries)) {
            return [];
        }

        return array_map(function ($entry) {
            return (object) [
                'account' => $entry['account'] ?? 'N/A',
                'debit' => $entry['debit'] ?? 0,
                'credit' => $entry['credit'] ?? 0,
                'type' => ($entry['debit'] ?? 0) > 0 ? 'debit' : 'credit'
            ];
        }, $this->entries);
    }

    // ============================================
    // METHODS
    // ============================================

    /**
     * Generate unique entry number.
     */
    public static function generateEntryNo($type = 'JE')
    {
        $prefix = $type . '-' . date('Ymd') . '-';
        $lastEntry = self::where('entry_no', 'LIKE', $prefix . '%')
                         ->orderBy('id', 'desc')
                         ->first();
        
        if ($lastEntry) {
            $lastNumber = intval(substr($lastEntry->entry_no, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Validate debit equals credit.
     */
    public function validateBalances()
    {
        return $this->total_debit == $this->total_credit;
    }

    /**
     * Approve the journal entry.
     */
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        
        return $this;
    }

    /**
     * Reject the journal entry.
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason ?? 'Rejected'
        ]);
        
        return $this;
    }

    /**
     * Create a journal entry from sale.
     */
    public static function createFromSale($sale)
    {
        $entries = [
            [
                'account' => 'Accounts Receivable',
                'debit' => $sale->grand_total,
                'credit' => 0
            ],
            [
                'account' => 'Sales Revenue',
                'debit' => 0,
                'credit' => $sale->subtotal
            ],
            [
                'account' => 'Liability - Cylinder Deposit',
                'debit' => 0,
                'credit' => $sale->cylinder_deposit_total ?? 0
            ]
        ];

        // Add COGS entry if needed
        // Add Tax entry if needed

        return self::create([
            'entry_no' => self::generateEntryNo('SALE'),
            'date' => $sale->date,
            'description' => 'Sale Invoice: ' . $sale->invoice_no,
            'sale_id' => $sale->id,
            'type' => 'sale',
            'total_debit' => $sale->grand_total,
            'total_credit' => $sale->subtotal + ($sale->cylinder_deposit_total ?? 0),
            'entries' => json_encode($entries),
            'created_by' => auth()->id(),
            'status' => 'approved'
        ]);
    }

    /**
     * Create a journal entry from purchase.
     */
    public static function createFromPurchase($purchase)
    {
        $entries = [
            [
                'account' => 'Inventory - Gas',
                'debit' => $purchase->subtotal,
                'credit' => 0
            ],
            [
                'account' => 'Accounts Payable',
                'debit' => 0,
                'credit' => $purchase->grand_total
            ]
        ];

        // Add cylinder asset entry if purchased
        if ($purchase->cylinder_purchase_total > 0) {
            $entries[] = [
                'account' => 'Fixed Asset - Cylinders',
                'debit' => $purchase->cylinder_purchase_total,
                'credit' => 0
            ];
        }

        // Add deposit entry if paid to supplier
        if ($purchase->cylinder_deposit_paid > 0) {
            $entries[] = [
                'account' => 'Cylinder Deposit (Supplier)',
                'debit' => $purchase->cylinder_deposit_paid,
                'credit' => 0
            ];
        }

        return self::create([
            'entry_no' => self::generateEntryNo('PUR'),
            'date' => $purchase->date,
            'description' => 'Purchase Invoice: ' . $purchase->purchase_invoice_no,
            'purchase_id' => $purchase->id,
            'type' => 'purchase',
            'total_debit' => $purchase->subtotal + $purchase->cylinder_purchase_total + $purchase->cylinder_deposit_paid,
            'total_credit' => $purchase->grand_total,
            'entries' => json_encode($entries),
            'created_by' => auth()->id(),
            'status' => 'approved'
        ]);
    }

    /**
     * Get total debits for an account.
     */
    public static function getTotalDebits($account, $startDate = null, $endDate = null)
    {
        $query = self::where('status', 'approved');
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        
        $entries = $query->get();
        
        $total = 0;
        foreach ($entries as $entry) {
            foreach ($entry->entries ?? [] as $item) {
                if (isset($item['account']) && $item['account'] === $account) {
                    $total += $item['debit'] ?? 0;
                }
            }
        }
        
        return $total;
    }

    /**
     * Get total credits for an account.
     */
    public static function getTotalCredits($account, $startDate = null, $endDate = null)
    {
        $query = self::where('status', 'approved');
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        
        $entries = $query->get();
        
        $total = 0;
        foreach ($entries as $entry) {
            foreach ($entry->entries ?? [] as $item) {
                if (isset($item['account']) && $item['account'] === $account) {
                    $total += $item['credit'] ?? 0;
                }
            }
        }
        
        return $total;
    }

    /**
     * Get account balance.
     */
    public static function getAccountBalance($account, $startDate = null, $endDate = null)
    {
        $debits = self::getTotalDebits($account, $startDate, $endDate);
        $credits = self::getTotalCredits($account, $startDate, $endDate);
        
        return $debits - $credits;
    }

    // ============================================
    // BOOT METHOD - Auto-generate entry number
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->entry_no)) {
                $model->entry_no = self::generateEntryNo();
            }
            
            if (empty($model->status)) {
                $model->status = 'pending';
            }
        });
    }
}
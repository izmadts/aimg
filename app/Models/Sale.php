<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'date',
        'delivery_date',
        'subtotal',
        'cylinder_deposit_total',
        'cylinder_sale_total',
        'discount',
        'tax',
        'grand_total',
        'amount_paid',
        'balance_due',
        'payment_status',
        'payment_method',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'cylinder_deposit_total' => 'decimal:2',
        'cylinder_sale_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function cylinderTransactions()
    {
        return $this->hasMany(CylinderTransaction::class, 'reference_document', 'invoice_no');
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
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
            'online' => '💳 Online Payment',
            'credit' => '📋 Credit'
        ];
        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => '📝 Draft',
            'confirmed' => '✅ Confirmed',
            'delivered' => '📦 Delivered',
            'cancelled' => '❌ Cancelled'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'paid' => '✅ Paid',
            'partial' => '🟡 Partial',
            'unpaid' => '❌ Unpaid'
        ];
        return $labels[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function getPaymentStatusColorAttribute()
    {
        $colors = [
            'paid' => 'green',
            'partial' => 'yellow',
            'unpaid' => 'red'
        ];
        return $colors[$this->payment_status] ?? 'gray';
    }

    public function getPaidPercentageAttribute()
    {
        if ($this->grand_total > 0) {
            return round(($this->amount_paid / $this->grand_total) * 100, 2);
        }
        return 0;
    }

    public function getPaymentProgressColorAttribute()
    {
        $percentage = $this->paid_percentage;
        if ($percentage >= 100) return 'bg-green-500';
        if ($percentage >= 50) return 'bg-yellow-500';
        if ($percentage > 0) return 'bg-orange-500';
        return 'bg-red-500';
    }

    // ============================================
    // METHODS
    // ============================================

    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total');
        $this->grand_total = $this->subtotal + $this->cylinder_deposit_total + $this->cylinder_sale_total - $this->discount + $this->tax;
        $this->balance_due = $this->grand_total - $this->amount_paid;
        $this->save();
        return $this;
    }

    public function updatePaymentStatus()
    {
        if ($this->amount_paid >= $this->grand_total) {
            $this->payment_status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
        $this->save();
        return $this;
    }

    public function recordPayment($amount, $method = 'cash', $date = null, $notes = null)
    {
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than 0.');
        }

        if ($amount > $this->balance_due) {
            throw new \Exception('Payment amount cannot exceed balance due.');
        }

        $this->amount_paid += $amount;
        $this->balance_due = $this->grand_total - $this->amount_paid;
        $this->updatePaymentStatus();
        $this->save();

        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => $date ?? now(),
            'transaction_no' => 'PAY-SALE-' . time() . '-' . rand(100, 999),
            'notes' => $notes,
            'created_by' => auth()->id(),
            'status' => 'completed'
        ]);

        return $payment;
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->invoice_no)) {
                $last = self::orderBy('id', 'desc')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $model->invoice_no = 'INV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
            if (empty($model->status)) {
                $model->status = 'confirmed';
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}
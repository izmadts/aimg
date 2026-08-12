<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_invoice_no',
        'supplier_id',
        'date',
        'delivery_date',
        'purchase_type',
        'gas_product_id',
        'gas_quantity',
        'gas_price',
        'gas_total',
        'cylinder_id',
        'cylinder_quantity',
        'cylinder_purchase_price',
        'cylinder_sale_price',
        'cylinder_total',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'amount_paid',
        'balance_due',
        'payment_status',
        'status',
        'debit_account_id',
        'credit_account_id',
        'reference_no',
        'notes',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'date' => 'date',
        'delivery_date' => 'date',
        'approved_at' => 'datetime',
        'gas_quantity' => 'decimal:2',
        'gas_price' => 'decimal:2',
        'gas_total' => 'decimal:2',
        'cylinder_quantity' => 'integer',
        'cylinder_purchase_price' => 'decimal:2',
        'cylinder_sale_price' => 'decimal:2',
        'cylinder_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gasProduct()
    {
        return $this->belongsTo(GasProduct::class);
    }

    public function cylinder()
    {
        return $this->belongsTo(Cylinder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function cylinderTransactions()
    {
        return $this->hasMany(SupplierCylinderTransaction::class);
    }

    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }

    // Accessors
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

    public function getPurchaseTypeLabelAttribute()
    {
        $labels = [
            'gas_only' => '🧪 Gas Only',
            'gas_with_cylinder' => '🧪 + 🛢️ Gas + Cylinder',
            'cylinder_only' => '🛢️ Cylinder Only',
            'exchange' => '🔄 Exchange'
        ];
        return $labels[$this->purchase_type] ?? ucfirst($this->purchase_type);
    }

    // Methods
    public function calculateTotals()
    {
        $this->subtotal = ($this->gas_total ?? 0) + ($this->cylinder_total ?? 0);
        $this->grand_total = $this->subtotal - ($this->discount ?? 0) + ($this->tax ?? 0);
        $this->balance_due = $this->grand_total - ($this->amount_paid ?? 0);
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

    public function approve()
    {
        $this->update([
            'status' => 'confirmed',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        return $this;
    }

    public function payments()
{
    return $this->hasMany(PurchasePayment::class);
}

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->purchase_invoice_no)) {
                $last = self::orderBy('id', 'desc')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $model->purchase_invoice_no = 'PO-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
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
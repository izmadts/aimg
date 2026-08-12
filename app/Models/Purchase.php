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
        'subtotal',
        'cylinder_total',
        'discount',
        'tax',
        'grand_total',
        'amount_paid',
        'balance_due',
        'payment_method',
        'payment_status',
        'status',
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
        'subtotal' => 'decimal:2',
        'cylinder_total' => 'decimal:2',
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

    public function accountingEntries()
    {
        return $this->morphMany(AccountingEntry::class, 'reference');
    }

    public function cylinderTransactions()
    {
        return $this->hasMany(SupplierCylinderTransaction::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
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

    /**
     * Recompute header totals from line items. Items must already be saved/loaded.
     */
    public function calculateTotals()
    {
        $items = $this->items()->get();

        $this->subtotal = $items->sum('gas_total');
        $this->cylinder_total = $items->where('cylinder_action', 'purchase')->sum('cylinder_total');
        $this->grand_total = $this->subtotal + $this->cylinder_total - $this->discount + $this->tax;
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

    public function approve()
    {
        $this->update([
            'status' => 'confirmed',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        return $this;
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

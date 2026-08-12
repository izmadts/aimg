<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CylinderTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cylinder_id',
        'customer_id',
        'user_id',
        'transaction_type',
        'condition',
        'transaction_date',
        'gas_quantity_at_transaction',
        'security_deposit_charged',
        'security_deposit_refunded',
        'damage_charge',
        'remarks',
        'reference_document'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'gas_quantity_at_transaction' => 'decimal:2',
        'security_deposit_charged' => 'decimal:2',
        'security_deposit_refunded' => 'decimal:2',
        'damage_charge' => 'decimal:2'
    ];
    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the cylinder associated with this transaction.
     */
    public function cylinder()
    {
        return $this->belongsTo(Cylinder::class);
    }

    /**
     * Get the customer associated with this transaction.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale associated with this transaction.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'reference_document', 'invoice_no');
    }

    /**
     * Get the purchase associated with this transaction.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'reference_document', 'purchase_invoice_no');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include issued transactions.
     */
    public function scopeIssued($query)
    {
        return $query->where('transaction_type', 'issued');
    }

    /**
     * Scope a query to only include returned transactions.
     */
    public function scopeReturned($query)
    {
        return $query->where('transaction_type', 'returned');
    }

    /**
     * Scope a query to only include sold transactions.
     */
    public function scopeSold($query)
    {
        return $query->where('transaction_type', 'sold');
    }

    /**
     * Scope a query to only include purchased transactions.
     */
    public function scopePurchased($query)
    {
        return $query->where('transaction_type', 'purchased');
    }

    /**
     * Scope a query to only include transactions by customer.
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include transactions by cylinder.
     */
    public function scopeByCylinder($query, $cylinderId)
    {
        return $query->where('cylinder_id', $cylinderId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to get current month transactions.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                     ->whereYear('transaction_date', now()->year);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Get the transaction type label.
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'issued' => 'Issued to Customer',
            'returned' => 'Returned from Customer',
            'sold' => 'Sold to Customer',
            'purchased' => 'Purchased New',
            'filled' => 'Gas Filled',
            'emptied' => 'Gas Emptied',
            'maintenance_in' => 'Sent to Maintenance',
            'maintenance_out' => 'Returned from Maintenance',
            'scrapped' => 'Scrapped',
            'received_filled' => 'Received Filled from Supplier',
            'received_empty' => 'Received Empty from Supplier',
            'returned_empty' => 'Returned Empty to Supplier',
            'exchanged' => 'Exchanged with Supplier'
        ];
        
        return $labels[$this->transaction_type] ?? ucfirst(str_replace('_', ' ', $this->transaction_type));
    }

    /**
     * Get the transaction color for badges.
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            'issued' => 'yellow',
            'returned' => 'green',
            'sold' => 'blue',
            'purchased' => 'purple',
            'filled' => 'green',
            'emptied' => 'gray',
            'maintenance_in' => 'orange',
            'maintenance_out' => 'blue',
            'scrapped' => 'red',
            'received_filled' => 'green',
            'received_empty' => 'gray',
            'returned_empty' => 'orange',
            'exchanged' => 'purple'
        ];
        
        return $colors[$this->transaction_type] ?? 'gray';
    }

    /**
     * Get the transaction party (customer or supplier).
     */
    public function getPartyAttribute()
    {
        if ($this->customer_id) {
            return $this->customer->name ?? 'Unknown Customer';
        }
        return 'N/A';
    }

    /**
     * Get the formatted amount (deposit or damage).
     */
    public function getFormattedAmountAttribute()
    {
        if ($this->security_deposit_charged > 0) {
            return 'Deposit: Rs. ' . number_format($this->security_deposit_charged, 2);
        }
        if ($this->security_deposit_refunded > 0) {
            return 'Refund: Rs. ' . number_format($this->security_deposit_refunded, 2);
        }
        if ($this->damage_charge > 0) {
            return 'Damage: Rs. ' . number_format($this->damage_charge, 2);
        }
        return null;
    }

    // ============================================
    // METHODS
    // ============================================

    /**
     * Check if transaction is for customer.
     */
    public function isCustomerTransaction()
    {
        return !is_null($this->customer_id);
    }

    /**
     * Check if transaction is issued.
     */
    public function isIssued()
    {
        return $this->transaction_type === 'issued';
    }

    /**
     * Check if transaction is returned.
     */
    public function isReturned()
    {
        return $this->transaction_type === 'returned';
    }

    /**
     * Check if transaction is sold.
     */
    public function isSold()
    {
        return $this->transaction_type === 'sold';
    }

    /**
     * Check if transaction is purchased.
     */
    public function isPurchased()
    {
        return $this->transaction_type === 'purchased';
    }

    /**
     * Get transaction summary.
     */
    public function getSummary()
    {
        $summary = $this->type_label;
        if ($this->customer_id) {
            $summary .= ' - ' . ($this->customer->name ?? 'Unknown');
        }
        if ($this->gas_quantity_at_transaction > 0) {
            $summary .= ' - ' . $this->gas_quantity_at_transaction . ' units';
        }
        return $summary;
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->transaction_date)) {
                $model->transaction_date = now();
            }
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }
}
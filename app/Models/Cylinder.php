<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cylinder extends Model
{
    use HasFactory;

    protected $fillable = [
        'cylinder_number',
        'gas_product_id',
        'type',
        'manufacturer',
        'tare_weight',
        'capacity',
        'stock_quantity',
        'issued_quantity',
        'purchase_price',
        'sale_price',
        'status',
        'supplier_id',
        'purchase_date',
        'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'tare_weight' => 'decimal:2',
        'capacity' => 'decimal:2',
        'stock_quantity' => 'integer',
        'issued_quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function gasProduct()
    {
        return $this->belongsTo(GasProduct::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(CylinderTransaction::class);
    }

    public function issuedDetails()
    {
        return $this->hasMany(CylinderIssuedDetail::class);
    }

    public function activeIssuedDetails()
    {
        return $this->hasMany(CylinderIssuedDetail::class)->where('status', 'issued');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeAvailable($query)
    {
        return $query->whereColumn('stock_quantity', '>', 'issued_quantity');
    }

    public function scopeInStock($query)
    {
        return $query->whereIn('status', ['in_house', 'partial_issued']);
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'under_maintenance');
    }

    public function scopeScrapped($query)
    {
        return $query->where('status', 'scrapped');
    }

    public function scopeByGas($query, $gasId)
    {
        return $query->where('gas_product_id', $gasId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'in_house' => '🏠 In House',
            'partial_issued' => '🟡 Partial Issued',
            'all_issued' => '🟠 All Issued',
            'out_of_stock' => '🚫 Out of Stock',
            'under_maintenance' => '🔧 Under Maintenance',
            'scrapped' => '❌ Scrapped',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'in_house' => 'blue',
            'partial_issued' => 'yellow',
            'all_issued' => 'orange',
            'out_of_stock' => 'red',
            'under_maintenance' => 'red',
            'scrapped' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->stock_quantity - ($this->issued_quantity ?? 0);
    }

    public function getTotalAssetValueAttribute()
    {
        return $this->purchase_price * $this->stock_quantity;
    }

    public function getTotalSaleValueAttribute()
    {
        return ($this->sale_price ?? 0) * $this->stock_quantity;
    }

    public function getProfitPerPieceAttribute()
    {
        return ($this->sale_price ?? 0) - $this->purchase_price;
    }

    public function getMarginAttribute()
    {
        if ($this->purchase_price > 0) {
            return round((($this->profit_per_piece) / $this->purchase_price) * 100, 2);
        }
        return 0;
    }

    public function getIsInStockAttribute()
    {
        return $this->available_quantity > 0;
    }

    public function getIsAllIssuedAttribute()
    {
        return $this->stock_quantity > 0 && $this->issued_quantity >= $this->stock_quantity;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock_quantity <= 0;
    }

    public function getCustomerListAttribute()
    {
        return $this->activeIssuedDetails()
            ->with('customer')
            ->get()
            ->map(function ($detail) {
                return (object) [
                    'customer' => $detail->customer,
                    'quantity' => $detail->quantity,
                    'issue_date' => $detail->issue_date,
                    'days_out' => $detail->days_out,
                ];
            });
    }

    // ============================================
    // JOURNEY / TIMELINE
    // ============================================

    public function getJourney()
    {
        $journey = [];
        $transactions = $this->transactions()
            ->with(['customer', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($transactions as $transaction) {
            $journey[] = (object) [
                'date' => $transaction->created_at,
                'date_formatted' => $transaction->created_at->format('d-m-Y H:i'),
                'type' => $transaction->transaction_type,
                'type_label' => $this->getTypeLabel($transaction->transaction_type),
                'party_name' => $transaction->customer->name ?? 'System',
                'gas_quantity' => $transaction->gas_quantity_at_transaction,
                'reference' => $transaction->reference_document,
                'remarks' => $transaction->remarks,
                'icon' => $this->getJourneyIcon($transaction->transaction_type),
                'color' => $this->getJourneyColor($transaction->transaction_type)
            ];
        }

        return $journey;
    }

    private function getJourneyIcon($type)
    {
        $icons = [
            'purchased' => 'fa-shopping-cart',
            'issued' => 'fa-hand-holding',
            'returned' => 'fa-undo',
            'sold' => 'fa-check-circle',
            'maintenance_in' => 'fa-tools',
            'maintenance_out' => 'fa-check',
            'scrapped' => 'fa-trash',
            'stock_update' => 'fa-boxes'
        ];
        return $icons[$type] ?? 'fa-circle';
    }

    private function getJourneyColor($type)
    {
        $colors = [
            'purchased' => 'purple',
            'issued' => 'yellow',
            'returned' => 'blue',
            'sold' => 'indigo',
            'maintenance_in' => 'orange',
            'maintenance_out' => 'green',
            'scrapped' => 'red',
            'stock_update' => 'blue'
        ];
        return $colors[$type] ?? 'gray';
    }

    private function getTypeLabel($type)
    {
        $labels = [
            'issued' => 'Issued to Customer',
            'returned' => 'Returned from Customer',
            'sold' => 'Sold to Customer',
            'purchased' => 'Purchased / Stock Added',
            'maintenance_in' => 'Sent to Maintenance',
            'maintenance_out' => 'Returned from Maintenance',
            'scrapped' => 'Scrapped',
            'stock_update' => 'Stock Updated'
        ];
        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public function getFullHistory()
    {
        $history = [];

        $transactions = $this->transactions()
            ->with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($transactions as $transaction) {
            $history[] = (object) [
                'date' => $transaction->created_at,
                'type' => $transaction->transaction_type,
                'type_label' => $this->getTypeLabel($transaction->transaction_type),
                'customer' => $transaction->customer,
                'user' => $transaction->user,
                'gas_quantity' => $transaction->gas_quantity_at_transaction,
                'security_deposit' => $transaction->security_deposit_charged,
                'security_deposit_refund' => $transaction->security_deposit_refunded,
                'damage_charge' => $transaction->damage_charge,
                'remarks' => $transaction->remarks,
                'reference_document' => $transaction->reference_document
            ];
        }

        return $history;
    }

    // ============================================
    // METHODS
    // ============================================

    public function updateStatus()
    {
        if ($this->stock_quantity <= 0) {
            $status = 'out_of_stock';
        } elseif ($this->issued_quantity >= $this->stock_quantity) {
            $status = 'all_issued';
        } elseif ($this->issued_quantity > 0) {
            $status = 'partial_issued';
        } else {
            $status = 'in_house';
        }

        $this->update(['status' => $status]);
        return $this;
    }

    public function addStock($quantity)
    {
        $this->increment('stock_quantity', $quantity);
        $this->updateStatus();
        return $this;
    }

    public function removeStock($quantity)
    {
        $locked = static::whereKey($this->id)->lockForUpdate()->first();
        if ($locked->available_quantity < $quantity) {
            throw new \Exception('Insufficient stock. Available: ' . $locked->available_quantity);
        }
        $this->decrement('stock_quantity', $quantity);
        $this->updateStatus();
        return $this;
    }

    public function issueToCustomer($customerId, $quantity = 1, $securityDeposit = 0, $reference = null)
    {
        $locked = static::whereKey($this->id)->lockForUpdate()->first();
        if ($locked->available_quantity < $quantity) {
            throw new \Exception('Insufficient stock. Available: ' . $locked->available_quantity);
        }

        $detail = $this->issuedDetails()->create([
            'customer_id' => $customerId,
            'quantity' => $quantity,
            'issue_date' => now(),
            'security_deposit' => $securityDeposit,
            'reference_document' => $reference,
            'status' => 'issued',
            'created_by' => auth()->id(),
        ]);

        $this->increment('issued_quantity', $quantity);
        $this->updateStatus();

        $this->transactions()->create([
            'customer_id' => $customerId,
            'user_id' => auth()->id(),
            'transaction_type' => 'issued',
            'transaction_date' => now(),
            'security_deposit_charged' => $securityDeposit,
            'remarks' => "Issued {$quantity} piece(s) to customer",
            'reference_document' => $reference,
        ]);

        return $detail;
    }

    public function returnFromCustomer($customerId, $quantity = 1, $damageCharge = 0, $refund = 0, $reference = null)
    {
        $details = $this->issuedDetails()
            ->where('customer_id', $customerId)
            ->where('status', 'issued')
            ->get();

        $totalIssued = $details->sum('quantity');

        if ($quantity > $totalIssued) {
            throw new \Exception('Cannot return more than issued. Issued: ' . $totalIssued);
        }

        $remainingQuantity = $quantity;
        foreach ($details as $detail) {
            if ($remainingQuantity <= 0) break;

            $returnQty = min($remainingQuantity, $detail->quantity);
            $detail->quantity -= $returnQty;

            if ($detail->quantity == 0) {
                $detail->status = 'returned';
                $detail->return_date = now();
            }
            $detail->save();

            $remainingQuantity -= $returnQty;
        }

        $this->decrement('issued_quantity', $quantity);
        $this->updateStatus();

        $this->transactions()->create([
            'customer_id' => $customerId,
            'user_id' => auth()->id(),
            'transaction_type' => 'returned',
            'transaction_date' => now(),
            'security_deposit_refunded' => $refund,
            'damage_charge' => $damageCharge,
            'remarks' => "Returned {$quantity} piece(s) from customer",
            'reference_document' => $reference,
        ]);

        return true;
    }

    public function sellToCustomer($customerId, $quantity = 1, $reference = null)
    {
        $locked = static::whereKey($this->id)->lockForUpdate()->first();
        if ($locked->available_quantity < $quantity) {
            throw new \Exception('Insufficient stock. Available: ' . $locked->available_quantity);
        }

        $this->decrement('stock_quantity', $quantity);
        $this->updateStatus();

        $this->transactions()->create([
            'customer_id' => $customerId,
            'user_id' => auth()->id(),
            'transaction_type' => 'sold',
            'transaction_date' => now(),
            'remarks' => "Sold {$quantity} piece(s) to customer",
            'reference_document' => $reference,
        ]);

        return true;
    }

    public function autoDetectSalePrice()
    {
        if ($this->gasProduct) {
            $salePrice = $this->purchase_price * 1.2;
            if ($this->gasProduct->sale_price && $this->gasProduct->sale_price > 0) {
                $salePrice = $this->gasProduct->sale_price;
            }
            $salePrice += $this->getCylinderPremium();
            return round($salePrice, 2);
        }
        return 0;
    }

    private function getCylinderPremium()
    {
        $premiums = [
            'B-D Type' => 500,
            'D-Type' => 800,
            'Jumbo' => 1500,
            'Small' => 300,
        ];
        return $premiums[$this->type] ?? 500;
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->cylinder_number)) {
                $last = self::orderBy('id', 'desc')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $model->cylinder_number = 'CYL-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            }
            if (empty($model->status)) {
                $model->status = 'in_house';
            }
            if (empty($model->issued_quantity)) {
                $model->issued_quantity = 0;
            }
        });
    }
}

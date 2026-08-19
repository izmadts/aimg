<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'uom',
        'density_kg_per_m3',
        'purchase_price',
        'sale_price',
        'current_stock',
        'minimum_stock_level',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'minimum_stock_level' => 'decimal:2',
        'density_kg_per_m3' => 'decimal:4'
    ];

    /**
     * Common industrial/medical gas densities in KG per cubic meter (at
     * standard conditions), used only to pre-fill the density field for a
     * recognized gas name — never applied automatically to stored data.
     */
    public const KNOWN_DENSITIES = [
        'oxygen' => 1.4290,
        'nitrogen' => 1.2506,
        'argon' => 1.7840,
        'carbon dioxide' => 1.9770,
        'acetylene' => 1.0970,
        'nitrous oxide' => 1.9770,
        'helium' => 0.1786,
        'hydrogen' => 0.0899,
        'ammonia' => 0.7290,
        'propane' => 1.8820,
    ];

    // Relationships
    public function cylinders()
    {
        return $this->hasMany(Cylinder::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('current_stock', '<=', 'minimum_stock_level')
                     ->where('is_active', true);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0)
                     ->where('is_active', true);
    }

    // Accessors
    public function getStockStatusAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        }
        if ($this->current_stock <= $this->minimum_stock_level) {
            return 'low';
        }
        if ($this->current_stock <= $this->minimum_stock_level * 2) {
            return 'medium';
        }
        return 'good';
    }

    public function getStockStatusLabelAttribute()
    {
        $labels = [
            'out_of_stock' => 'Out of Stock',
            'low' => 'Low Stock',
            'medium' => 'Medium Stock',
            'good' => 'Good Stock'
        ];
        return $labels[$this->stock_status] ?? 'Unknown';
    }

    public function getStockStatusColorAttribute()
    {
        $colors = [
            'out_of_stock' => 'red',
            'low' => 'yellow',
            'medium' => 'orange',
            'good' => 'green'
        ];
        return $colors[$this->stock_status] ?? 'gray';
    }

    public function getStockValueAttribute()
    {
        return $this->current_stock * $this->purchase_price;
    }

    /**
     * Current stock converted to KG, when it's meaningful to show one:
     * already in KG (identity), or in Cubic Meter with a density on file.
     * Null otherwise — nothing to convert from/to.
     */
    public function getStockInKgAttribute()
    {
        $uom = mb_strtolower(trim($this->uom ?? ''));

        if ($uom === 'kg' || $uom === 'kilogram') {
            return (float) $this->current_stock;
        }

        if (str_contains($uom, 'cubic meter') && $this->density_kg_per_m3 > 0) {
            return round((float) $this->current_stock * (float) $this->density_kg_per_m3, 2);
        }

        return null;
    }

    // Methods
    public function updateStock($quantity, $type = 'add')
    {
        if ($type === 'add') {
            $this->increment('current_stock', $quantity);
        } else {
            $this->decrement('current_stock', $quantity);
        }
        return $this;
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock_level;
    }

    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }
}
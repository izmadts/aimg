<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CylinderIssuedDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'cylinder_id',
        'customer_id',
        'quantity',
        'issue_date',
        'return_date',
        'security_deposit',
        'reference_document',
        'status',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'return_date' => 'date',
        'quantity' => 'integer',
        'security_deposit' => 'decimal:2'
    ];

    public function cylinder()
    {
        return $this->belongsTo(Cylinder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function getDaysOutAttribute()
    {
        if ($this->status === 'issued' && $this->issue_date) {
            return $this->issue_date->diffInDays(now());
        }
        return 0;
    }
}
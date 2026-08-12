<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'advance_date',
        'deduction_start_month',
        'deduction_installments',
        'deduction_amount_per_month',
        'status',
        'purpose',
        'approved_by',
        'approved_at',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'advance_date' => 'date',
        'deduction_start_month' => 'date',
        'deduction_amount_per_month' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'cnic',
        'designation',
        'department',
        'joining_date',
        'leaving_date',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'bank_name',
        'bank_account',
        'is_active',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getTotalAdvanceAttribute()
    {
        return $this->advances()->where('status', 'approved')->sum('amount');
    }

    public function getTotalLeaveAttribute()
    {
        return $this->leaves()->where('status', 'approved')->count();
    }

    public function getPresentDaysAttribute()
    {
        return $this->attendances()->where('status', 'present')->count();
    }

    public function getAbsentDaysAttribute()
    {
        return $this->attendances()->where('status', 'absent')->count();
    }

    public function getLateDaysAttribute()
    {
        return $this->attendances()->where('status', 'late')->count();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->employee_code)) {
                $last = self::orderBy('id', 'desc')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $model->employee_code = 'EMP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}
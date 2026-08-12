<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'profile_image',
        'role_id',
        'is_active',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Accessor for initials
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        return $initials ?: 'U';
    }

    // Accessor for profile image URL
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'created_by');
    }

    public function cylinderTransactions()
    {
        return $this->hasMany(CylinderTransaction::class, 'user_id');
    }

    public function supplierCylinderTransactions()
    {
        return $this->hasMany(SupplierCylinderTransaction::class, 'user_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(AccountingEntry::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmin($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('slug', 'admin'));
    }

    // Methods
    public function isAdmin()
    {
        return $this->role?->slug === 'admin';
    }

    public function isStaff()
    {
        return $this->role && $this->role->slug !== 'admin';
    }

    public function hasPermission(string $slug): bool
    {
        return (bool) $this->role?->hasPermission($slug);
    }

    public function getRoleNameAttribute()
    {
        return $this->role->name ?? 'No Role';
    }
}
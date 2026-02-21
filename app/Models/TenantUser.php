<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tenant User Model
 * 
 * Represents a user within a tenant's store (staff, managers, etc.).
 * This is separate from the central User model and exists in each tenant's database.
 * 
 * @extends TenantModel
 */
class TenantUser extends TenantModel implements Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'name',
        'language',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get the authentication password for the user.
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope a query to only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only users belonging to current tenant.
     * 
     * Note: This is automatically applied by BelongsToTenant trait,
     * but included here for explicit clarity.
     */
    public function scopeForCurrentTenant($query)
    {
        return $query; // Already scoped by BelongsToTenant
    }
}

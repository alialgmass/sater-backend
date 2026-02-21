<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Admin User Model
 * 
 * Represents a platform administrator with access to the central admin panel.
 * Admin users are separate from tenant users and have platform-level privileges.
 * 
 * @extends Authenticatable
 */
class AdminUser extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'is_active',
        'last_login_at',
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
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the authentication password for the user.
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope a query to only active admin users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only super admins.
     */
    public function scopeSuperAdmin($query)
    {
        return $query->where('is_super_admin', true);
    }
}

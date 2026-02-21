<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant Model
 * 
 * Represents a merchant's store account in the multi-tenancy system.
 * Each tenant has an isolated database and can have multiple domains.
 * 
 * @extends BaseTenant
 * @implements TenantWithDatabase
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_name',
        'email',
        'password_hash',
        'language',
        'status',
        'current_plan_id',
        'suspension_reason',
        'deletion_scheduled_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password_hash' => 'hashed',
        'deletion_scheduled_at' => 'datetime',
    ];

    /**
     * Get the tenant's subscription plan.
     */
    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the tenant's subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if tenant is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if tenant is pending email verification.
     */
    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_email_verification';
    }

    /**
     * Scope a query to only active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only suspended tenants.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription Model
 * 
 * Tracks a tenant's subscription history and current plan.
 * Each subscription has a billing cycle (monthly/yearly) and status.
 * 
 * @extends Model
 */
class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'trial_ends_at',
        'currency',
        'amount',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Check if the subscription is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null 
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if the subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Get the remaining trial days.
     */
    public function getRemainingTrialDays(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Scope a query to only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope a query to subscriptions that are on trial.
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope a query to subscriptions ending soon (within X days).
     * 
     * @param int $days Number of days to check
     */
    public function scopeEndingSoon($query, int $days = 7)
    {
        return $query->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->addDays($days));
    }
}

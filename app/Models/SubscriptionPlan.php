<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subscription Plan Model
 * 
 * Defines subscription tiers with features and pricing for tenants.
 * Each plan has monthly and yearly pricing options and a set of feature limits.
 * 
 * @extends Model
 */
class SubscriptionPlan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'features',
        'trial_days',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the tenants subscribed to this plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'current_plan_id');
    }

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get a specific feature value.
     * 
     * @param string $key Feature key
     * @param mixed $default Default value if feature doesn't exist
     * @return mixed Feature value or default
     */
    public function getFeature(string $key, mixed $default = null): mixed
    {
        return $this->features[$key] ?? $default;
    }

    /**
     * Check if the plan has a specific feature.
     * 
     * @param string $key Feature key
     * @return bool True if feature exists, false otherwise
     */
    public function hasFeature(string $key): bool
    {
        return isset($this->features[$key]);
    }

    /**
     * Check if the plan is active and available for selection.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope a query to only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

<?php

namespace App\Models;

/**
 * Product Model (Tenant-Scoped)
 * 
 * Example tenant-scoped model for products.
 * All queries are automatically scoped to the current tenant.
 * 
 * @extends TenantModel
 */
class Product extends TenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'compare_at_price',
        'cost_per_item',
        'track_inventory',
        'quantity',
        'low_stock_threshold',
        'status', // draft, active, archived
        'language',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_per_item' => 'decimal:2',
        'track_inventory' => 'boolean',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Scope a query to only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return !$this->track_inventory || $this->quantity > 0;
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->track_inventory 
            && $this->quantity <= $this->low_stock_threshold;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Model (Tenant-Scoped)
 * 
 * Example tenant-scoped model for customer orders.
 * All queries are automatically scoped to the current tenant.
 * 
 * @extends TenantModel
 */
class Order extends TenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_email',
        'customer_name',
        'status',
        'payment_status',
        'fulfillment_status',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'total',
        'currency',
        'notes',
        'shipping_address',
        'billing_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'SAR',
    ];

    /**
     * Scope a query to only active orders.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'refunded']);
    }

    /**
     * Scope a query to pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}

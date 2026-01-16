<?php

namespace Modules\Checkout\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Modules\Checkout\Models\AppliedCoupon;
use Modules\Checkout\Enums\CheckoutStatusEnum;

class CheckoutSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'shipping_address' => 'array',
        'status' => CheckoutStatusEnum::class,
        'expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\Modules\Auth\Models\Customer::class);
    }

    public function appliedCoupons()
    {
        return $this->hasMany(AppliedCoupon::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())->where('status', '!=', 'completed');
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }
}

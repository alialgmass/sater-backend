<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;

use Modules\Cart\Enums\CartItemStatusEnum;

class GuestCart extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price_at_add_time' => 'decimal:2',
        'status' => CartItemStatusEnum::class,
    ];

    public function product()
    {
        return $this->belongsTo(\Modules\Product\Models\Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\Modules\Vendor\Models\Vendor::class);
    }

    public function scopeByCartKey($query, string $cartKey)
    {
        return $query->where('cart_key', $cartKey);
    }
}

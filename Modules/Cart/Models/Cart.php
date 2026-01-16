<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;


class Cart extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(\Modules\Auth\Models\Customer::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function vendors()
    {
        return $this->hasManyThrough(
            \Modules\Vendor\Models\Vendor::class,
            CartItem::class,
            'cart_id',
            'id',
            'id',
            'vendor_id'
        )->distinct();
    }
}

<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;


class SavedCartItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price_at_add_time' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\Modules\Auth\Models\Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Product\Models\Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\Modules\Vendor\Models\Vendor::class);
    }
}

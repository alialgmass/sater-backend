<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Model;


class AppliedCoupon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function checkoutSession()
    {
        return $this->belongsTo(CheckoutSession::class);
    }

    public function masterOrder()
    {
        return $this->belongsTo(\Modules\Order\Models\Order::class, 'master_order_id');
    }
}

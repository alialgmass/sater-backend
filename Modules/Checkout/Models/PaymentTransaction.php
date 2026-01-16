<?php

namespace Modules\Checkout\Models;

use Illuminate\Database\Eloquent\Model;

use Modules\Checkout\Enums\PaymentStatusEnum;
use Modules\Checkout\Enums\PaymentMethodEnum;

class PaymentTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PaymentStatusEnum::class,
        'payment_method' => PaymentMethodEnum::class,
        'gateway_response' => 'array',
    ];

    public function vendorOrder()
    {
        return $this->belongsTo(\Modules\Order\Models\Order::class, 'vendor_order_id');
    }
}

<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Order\Enums\OrderStatusEnum;
use Modules\Order\Enums\PaymentStatusEnum;
use Modules\Order\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'shipping_fees',
        'payment_method',
        'payment_status',
        'status',
        'shipping_address',
        'shipping_provider',
        'shipping_tracking',
    ];

    protected $casts = [
        'shipping_address' => 'json',
        'status' => OrderStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendorOrders(): HasMany
    {
        return $this->hasMany(VendorOrder::class, 'master_order_id');
    }


    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}

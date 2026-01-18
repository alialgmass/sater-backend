<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_order_id',
        'product_id',
        'quantity',
        'price',
        'product_name',
        'options',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


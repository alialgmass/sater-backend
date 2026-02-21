<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Order\Models\Order;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'order_shipments';

    protected $fillable = [
        'order_id',
        'vendor_id',
        'shipping_method_id',
        'courier_name',
        'tracking_number',
        'status',
        'estimated_delivery_from',
        'estimated_delivery_to',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'vendor_id' => 'integer',
        'shipping_method_id' => 'integer',
        'status' => 'string',
        'estimated_delivery_from' => 'datetime',
        'estimated_delivery_to' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function vendor()
    {
        return $this->belongsTo(\Modules\Vendor\Models\Vendor::class, 'vendor_id');
    }

    public function method()
    {
        return $this->belongsTo(\App\Models\Shipping\VendorShippingMethod::class, 'shipping_method_id');
    }

    public function attempts()
    {
        return $this->hasMany(\App\Models\Shipping\DeliveryAttempt::class);
    }
}
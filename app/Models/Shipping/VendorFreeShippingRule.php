<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorFreeShippingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'shipping_zone_id',
        'min_order_amount',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'shipping_zone_id' => 'integer',
        'min_order_amount' => 'decimal',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
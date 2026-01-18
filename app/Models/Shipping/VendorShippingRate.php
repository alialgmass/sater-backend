<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_shipping_method_id',
        'shipping_zone_id',
        'min_weight',
        'max_weight',
        'price',
    ];

    protected $casts = [
        'vendor_shipping_method_id' => 'integer',
        'shipping_zone_id' => 'integer',
        'min_weight' => 'decimal',
        'max_weight' => 'decimal',
        'price' => 'decimal',
    ];

    // Relationships
    public function method()
    {
        return $this->belongsTo(VendorShippingMethod::class, 'vendor_shipping_method_id');
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
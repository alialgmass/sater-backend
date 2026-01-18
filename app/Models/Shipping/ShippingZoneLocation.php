<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZoneLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'country',
        'region',
        'city',
    ];

    protected $casts = [
        'shipping_zone_id' => 'integer',
        'region' => 'string',
        'city' => 'string',
    ];

    // Relationships
    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
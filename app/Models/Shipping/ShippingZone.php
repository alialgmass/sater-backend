<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Relationships
    public function locations()
    {
        return $this->hasMany(ShippingZoneLocation::class);
    }

    public function rates()
    {
        return $this->hasMany(VendorShippingRate::class);
    }

    public function freeShippingRules()
    {
        return $this->hasMany(VendorFreeShippingRule::class);
    }
}
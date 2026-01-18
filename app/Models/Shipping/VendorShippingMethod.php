<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'is_cod',
        'min_delivery_days',
        'max_delivery_days',
        'is_active',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'is_cod' => 'boolean',
        'min_delivery_days' => 'integer',
        'max_delivery_days' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function rates()
    {
        return $this->hasMany(VendorShippingRate::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'shipping_method_id');
    }
}
<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $table = 'vendor_shipments';

    protected $fillable = [
        'vendor_order_id',
        'courier_name',
        'tracking_number',
        'tracking_url',
    ];

    protected $casts = [
        'tracking_url' => 'string',
    ];

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }
}


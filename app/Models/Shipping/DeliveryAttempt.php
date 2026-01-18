<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'attempt_number',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'shipment_id' => 'integer',
        'attempt_number' => 'integer',
        'status' => 'string',
        'failure_reason' => 'string',
    ];

    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}
<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;

use Modules\Customer\Enums\AddressLabelEnum;

class CustomerAddress extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'label' => AddressLabelEnum::class,
    ];

    public function customer()
    {
        return $this->belongsTo(\Modules\Auth\Models\Customer::class);
    }
}

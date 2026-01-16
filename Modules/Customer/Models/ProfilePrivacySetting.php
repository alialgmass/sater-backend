<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePrivacySetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'share_phone' => 'boolean',
        'share_name' => 'boolean',
        'share_address' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(\Modules\Auth\Models\Customer::class);
    }
}

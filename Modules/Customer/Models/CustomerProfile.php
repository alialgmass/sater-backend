<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Enums\GenderEnum;

class CustomerProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_of_birth' => 'date',
        'gender' => GenderEnum::class,
    ];

    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }
}

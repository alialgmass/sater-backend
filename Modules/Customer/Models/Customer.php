<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'phone' => RawPhoneNumberCast::class.'EG'
    ];

    public function profile()
    {
        return $this->hasOne(\Modules\Customer\Models\CustomerProfile::class);
    }

    public function addresses()
    {
        return $this->hasMany(\Modules\Customer\Models\CustomerAddress::class);
    }

    public function privacySettings()
    {
        return $this->hasOne(\Modules\Customer\Models\ProfilePrivacySetting::class);
    }

    public function orders()
    {
        return $this->hasMany(\Modules\Order\Models\Order::class);
    }

}

<?php

namespace Modules\Auth\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
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
}

<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

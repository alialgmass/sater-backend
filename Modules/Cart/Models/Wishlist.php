<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Wishlist extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    public function items()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function generateShareToken(): string
    {
        $this->share_token = Str::random(32);
        $this->save();

        return $this->share_token;
    }
}

<?php

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;


class WishlistItem extends Model
{
    protected $guarded = [];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Product\Models\Product::class);
    }
}

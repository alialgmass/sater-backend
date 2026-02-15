<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'hex_code'];

    protected static function newFactory()
    {
        return \Modules\Product\Database\Factories\ColorFactory::new();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'color_product');
    }
}

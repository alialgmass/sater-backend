<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'abbreviation'];

    protected static function newFactory()
    {
        return \Modules\Product\Database\Factories\SizeFactory::new();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size');
    }
}

<?php

namespace Modules\Review\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Review\Database\Factories\ReviewFactory;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'customer_id',
        'rating',
        'comment',
        'approved',
    ];

    public function product()
    {
        return $this->belongsTo(\Modules\Product\Models\Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(\Modules\Auth\Models\Customer::class, 'customer_id');
    }
}

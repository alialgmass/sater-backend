<?php

namespace Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Stock\Database\Factories\StockMovementFactory;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): StockMovementFactory
    // {
    //     // return StockMovementFactory::new();
    // }
}

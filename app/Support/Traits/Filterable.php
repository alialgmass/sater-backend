<?php

namespace App\Support\Traits;

use App\Support\Contracts\Filters\FilterContract;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

trait Filterable
{
    /**
     * @param  array<FilterContract>  $filters
     */
    public function scopeFilter(Builder $query, array $filters)
    {
        return app(Pipeline::class)
            ->send($query)
            ->through($filters)
            ->thenReturn();
    }
}

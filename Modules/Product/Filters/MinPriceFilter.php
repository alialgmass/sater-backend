<?php

namespace Modules\Product\Filters;

use App\Support\Contracts\Filters\FilterContract;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class MinPriceFilter implements FilterContract
{
    public function __construct(private ?float $minPrice) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->minPrice) {
            $query->where('price', '>=', $this->minPrice);
        }

        return $next($query);
    }
}

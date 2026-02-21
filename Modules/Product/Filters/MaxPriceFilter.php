<?php

namespace Modules\Product\Filters;

use App\Support\Contracts\Filters\FilterContract;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class MaxPriceFilter implements FilterContract
{
    public function __construct(private ?float $maxPrice) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->maxPrice) {
            $query->where('price', '<=', $this->maxPrice);
        }

        return $next($query);
    }
}

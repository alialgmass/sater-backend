<?php

namespace Modules\Product\Filters;

use App\Support\Contracts\Filters\FilterContract;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class ColorFilter implements FilterContract
{
    public function __construct(private ?int $colorId) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->colorId) {
            $query->whereHas('colors', function ($q) {
                $q->where('colors.id', $this->colorId);
            });
        }

        return $next($query);
    }
}

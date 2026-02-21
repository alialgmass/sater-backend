<?php

namespace Modules\Product\Filters;

use App\Support\Contracts\Filters\FilterContract;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter implements FilterContract
{
    public function __construct(private int|array|null $categoryId) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->categoryId) {
            is_array($this->categoryId)
                ? $query->whereIn('category_id', $this->categoryId)
                : $query->where('category_id', $this->categoryId);
        }

        return $next($query);
    }
}

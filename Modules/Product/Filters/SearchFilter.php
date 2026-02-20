<?php

namespace Modules\Product\Filters;

use App\Support\Contracts\Filters\FilterContract;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter implements FilterContract
{
    public function __construct(private ?string $search) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        return $next($query);
    }
}

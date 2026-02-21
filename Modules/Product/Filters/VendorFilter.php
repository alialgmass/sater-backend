<?php

namespace Modules\Product\Filters;

use App\Support\Contracts\Filters\FilterContract;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;

class VendorFilter implements FilterContract
{
    public function __construct(private ?int $vendorId) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->vendorId) {
            $query->where('vendor_id', $this->vendorId);
        }

        return $next($query);
    }
}

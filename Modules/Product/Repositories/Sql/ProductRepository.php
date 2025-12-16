<?php

namespace Modules\Product\Repositories\Sql;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Repositories\Contracts\ProductRepositoryContract;

class ProductRepository implements ProductRepositoryContract
{

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['vendor_id'] ?? null, fn ($q, $v) => $q->where('vendor_id', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where('name', 'like', "%{$v}%")
                    ->orWhere('sku', 'like', "%{$v}%");
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }
}

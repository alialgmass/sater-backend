<?php

namespace Modules\Product\Repositories\Sql;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Models\Product;
use Modules\Product\Repositories\Contracts\ProductRepositoryContract;

class ProductRepository implements ProductRepositoryContract
{

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['vendor_id'] ?? null, fn ($q, $v) => $q->where('vendor_id', $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['color_id'] ?? null, fn ($q, $v) => $q->whereHas('colors', fn($q) => $q->where('colors.id', $v)))
            ->when($filters['size_id'] ?? null, fn ($q, $v) => $q->whereHas('sizes', fn($q) => $q->where('sizes.id', $v)))
            ->when($filters['tag_id'] ?? null, fn ($q, $v) => $q->whereHas('tags', fn($q) => $q->where('tags.id', $v)))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where('name', 'like', "%{$v}%")
                    ->orWhere('sku', 'like', "%{$v}%");
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }
}

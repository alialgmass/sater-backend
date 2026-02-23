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
            ->when($filters['category_id'] ?? null, function ($q, $v) {
                if (is_array($v)) {
                    return $q->whereIn('category_id', $v);
                }
                return $q->where('category_id', $v);
            })
            ->when($filters['color_id'] ?? null, fn ($q, $v) => $q->whereHas('colors', fn($q) => $q->where('colors.id', $v)))
            ->when($filters['size_id'] ?? null, fn ($q, $v) => $q->whereHas('sizes', fn($q) => $q->where('sizes.id', $v)))
            ->when($filters['tag_id'] ?? null, fn ($q, $v) => $q->whereHas('tags', fn($q) => $q->where('tags.id', $v)))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['min_price'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['search'] ?? null, function ($q, $v) {
                $q->where('name', 'like', "%{$v}%")
                    ->orWhere('sku', 'like', "%{$v}%");
            })
            ->when($filters['featured'] ?? null, fn ($q, $v) => $q->where('attributes->featured', true))
            ->when($filters['sort'] ?? null, function ($q, $v) {
                switch ($v) {
                    case 'popular':
                        return $q->orderBy('sales_count', 'desc');
                    case 'price_asc':
                        return $q->orderBy('price', 'asc');
                    case 'price_desc':
                        return $q->orderBy('price', 'desc');
                    case 'rating':
                        return $q->orderBy('avg_rating', 'desc');
                    default:
                        return $q->latest();
                }
            }, fn($q) => $q->latest())
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['vendor', 'category', 'colors', 'sizes', 'tags'])->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::with(['vendor', 'category', 'colors', 'sizes', 'tags'])->where('slug', $slug)->first();
    }
}

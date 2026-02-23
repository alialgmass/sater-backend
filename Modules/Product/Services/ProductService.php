<?php

namespace Modules\Product\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Repositories\Contracts\ProductRepositoryContract;

class ProductService
{
    public function __construct(
        protected ProductRepositoryContract $products
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->products->paginate($filters);
    }

    public function find(string|int $identifier): ?\Modules\Product\Models\Product
    {
        if (is_numeric($identifier)) {
            return $this->products->findById((int) $identifier);
        }

        return $this->products->findBySlug($identifier);
    }
}

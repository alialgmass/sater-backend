<?php

namespace Modules\Product\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryContract
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?\Modules\Product\Models\Product;

    public function findBySlug(string $slug): ?\Modules\Product\Models\Product;
}

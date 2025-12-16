<?php

namespace Modules\Product\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryContract
{
    public function paginate(array $filters = []): LengthAwarePaginator;
}

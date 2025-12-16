<?php

namespace Modules\Category\Repositories\Sql;

use Modules\Category\Models\Category;
use Modules\Category\Repositories\Contracts\CategoryRepositoryContract;

class CategoryRepository implements CategoryRepositoryContract
{
    public function all()
    {
        return Category::with('children')->get();
    }
}

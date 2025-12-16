<?php

namespace Modules\Category\Services;

use Modules\Category\Repositories\Sql\CategoryRepository;

class CategoryService
{
    public function __construct(public CategoryRepository $categoryRepository)
    {

    }
    public function list()
    {
        return $this->categoryRepository->all();
    }
}

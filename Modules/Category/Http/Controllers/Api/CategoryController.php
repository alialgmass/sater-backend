<?php

namespace Modules\Category\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use Modules\Category\Services\CategoryService;

class CategoryController extends ApiController
{
    public function __construct(protected CategoryService $categoryService)
    {

    }

    public function index()
    {
      return  $this->apiBody([
            'categories' => $this->categoryService->list()
        ])->apiResponse();
    }
}

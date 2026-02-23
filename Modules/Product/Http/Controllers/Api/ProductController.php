<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Services\ProductService;

class ProductController extends ApiController
{
    public function __construct(
        protected ProductService $service
    )
    {
    }

    public function index(Request $request)
    {
        $products = $this->service->list($request->only([
            'vendor_id',
            'category_id',
            'color_id',
            'size_id',
            'tag_id',
            'status',
            'search',
            'min_price',
            'max_price',
            'on_sale',
            'featured',
            'sort',
            'per_page'
        ]));

        return $this->apiBody(
            ['products' =>
                ProductResource::paginate($products)
            ])->apiResponse();
    }

    public function show($identifier)
    {
        $product = $this->service->find($identifier);

        if (!$product) {
            return $this->apiMessage('Product not found')->apiCode(404)->apiResponse();
        }

        return $this->apiBody(['product' => new ProductResource($product)])->apiResponse();
    }
}

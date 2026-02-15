<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Modules\Product\Models\Size;

class SizeController extends ApiController
{
    public function index()
    {
        return $this->apiBody([
            'sizes' => Size::select('id', 'name', 'abbreviation')->get()
        ])->apiResponse();
    }
}

<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Modules\Product\Models\Color;

class ColorController extends ApiController
{
    public function index()
    {
        return $this->apiBody([
            'colors' => Color::select('id', 'name', 'hex_code')->get()
        ])->apiResponse();
    }
}

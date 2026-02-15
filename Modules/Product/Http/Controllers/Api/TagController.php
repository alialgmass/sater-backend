<?php

namespace Modules\Product\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Modules\Product\Models\Tag;

class TagController extends ApiController
{
    public function index()
    {
        return $this->apiBody([
            'tags' => Tag::select('id', 'name', 'slug')->get()
        ])->apiResponse();
    }
}

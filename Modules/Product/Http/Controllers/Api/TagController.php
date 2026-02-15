<?php

namespace Modules\Product\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\Product\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Tag::select('id', 'name', 'slug')->get()
        ]);
    }
}

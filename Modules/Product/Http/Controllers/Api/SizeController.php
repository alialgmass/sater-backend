<?php

namespace Modules\Product\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\Product\Models\Size;

class SizeController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Size::select('id', 'name', 'abbreviation')->get()
        ]);
    }
}

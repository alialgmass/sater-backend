<?php

namespace Modules\Product\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\Product\Models\Color;

class ColorController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Color::select('id', 'name', 'hex_code')->get()
        ]);
    }
}

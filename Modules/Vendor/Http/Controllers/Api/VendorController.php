<?php

namespace Modules\Vendor\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Modules\Vendor\Http\Resources\VendorResource;
use Modules\Vendor\Services\VendorService;

class VendorController extends ApiController
{
    public function index(Request $request, VendorService $vendorService)
    {
        $vendors = $vendorService->listActiveVendors();
        return $this->apiBody([
            'vendors' => VendorResource::paginate($vendors)
        ])->apiResponse();
    }
}

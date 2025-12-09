<?php

namespace Modules\Vendor\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Vendor\DTOs\VendorDTO;
use Modules\Vendor\Http\Requests\VendorRegisterRequest;
use Modules\Vendor\Http\Resources\VendorResource;
use Modules\Vendor\Services\VendorService;

class VendorAuthController extends ApiController
{
    public function __construct(
        private VendorService $vendorService
    )
    {
    }

    /**
     * Register a new vendor.
     * @throws Exception
     */
    public function register(VendorRegisterRequest $request): JsonResponse
    {
        $dto = VendorDTO::fromRequest($request);
        $vendor = $this->vendorService->register($dto);
        return $this->apiBody([
            'vendor' => new VendorResource($vendor),
        ])->apiResponse();
    }



    /**
     * Check if slug is available.
     */
    public function checkSlug(string $slug): JsonResponse
    {
        $available = $this->vendorService->isSlugAvailable($slug);

        return $this->apiBody([
            'available' => $available,
            'slug' => $slug,
        ])->apiResponse();
    }

}

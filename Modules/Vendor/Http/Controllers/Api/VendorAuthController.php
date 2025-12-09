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
     * Get vendor by slug.
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $vendor = $this->vendorService->getBySlug($slug);

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'vendor' => new VendorResource($vendor),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if slug is available.
     */
    public function checkSlug(string $slug): JsonResponse
    {
        $available = $this->vendorService->isSlugAvailable($slug);

        return response()->json([
            'available' => $available,
            'slug' => $slug,
        ]);
    }

    /**
     * Get all active vendors.
     */
    public function index(): JsonResponse
    {
        try {
            $vendors = $this->vendorService->getActiveVendors();

            return response()->json([
                'success' => true,
                'vendors' => VendorResource::collection($vendors),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search vendors.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        try {
            $vendors = $this->vendorService->search($request->query);

            return response()->json([
                'success' => true,
                'vendors' => VendorResource::collection($vendors),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

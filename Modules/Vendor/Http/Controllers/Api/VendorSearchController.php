<?php

namespace Modules\Vendor\Http\Controllers\Api;

use App\DTOs\ProductSearchDTO;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Modules\Product\Http\Resources\ProductSearchResource;
use Modules\Product\Services\Search\SearchService;
use Modules\Vendor\Models\Vendor;

/**
 * Vendor Store Search Controller
 *
 * Handles product search scoped to a specific vendor's store
 */
class VendorSearchController extends ApiController
{
    public function __construct(
        protected SearchService $searchService,
    ) {}

    /**
     * Search products in vendor's store
     *
     * GET /api/v1/vendors/{vendor_id}/search
     *
     * Same filters and sorting as global search, but results limited to vendor's products
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, int $vendorId)
    {
        // Verify vendor exists and is active
        $vendor = Vendor::where('id', $vendorId)
            ->active()
            ->firstOr(fn() => $this->notFound('Vendor not found'));

        // Validate request
        $validated = $request->validate(ProductSearchDTO::rules());

        // Create DTO and force vendor_id
        $dto = ProductSearchDTO::from($validated);
        $dto->vendor_id = $vendorId;

        // Execute search
        $results = $this->searchService->search($dto);

        if ($results->isEmpty()) {
            return $this->apiBody([
                'products' => [],
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'shop_name' => $vendor->shop_name,
                ],
                'message' => 'No products found in this store',
            ])->apiMessage('No products found')->apiResponse(202);
        }

        return $this->apiBody([
            'products' => ProductSearchResource::paginate($results),
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'shop_name' => $vendor->shop_name,
                'shop_slug' => $vendor->shop_slug,
                'description' => $vendor->description,
            ],
        ])->apiResponse();
    }
}

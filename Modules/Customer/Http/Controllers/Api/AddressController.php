<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\DTOs\CreateAddressData;
use Modules\Customer\Http\Requests\AddressRequest;
use Modules\Customer\Services\AddressService;
use Modules\Customer\Transformers\CustomerAddressResource;
use Modules\Customer\Models\CustomerAddress;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses;
        return response()->json(CustomerAddressResource::collection($addresses));
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $data = CreateAddressData::fromRequest($request);
        $address = $this->addressService->createAddress($request->user(), $data);

        return response()->json([
            'message' => 'Address added successfully.',
            'data' => new CustomerAddressResource($address),
        ], 201);
    }

    public function update(AddressRequest $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('update', $address);
        
        $data = CreateAddressData::fromRequest($request); // Reuse generic DTO or create specific one
        $updatedAddress = $this->addressService->updateAddress($request->user(), $address, $data);

        return response()->json([
            'message' => 'Address updated successfully.',
            'data' => new CustomerAddressResource($updatedAddress),
        ]);
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);
        
        $this->addressService->deleteAddress($request->user(), $address);

        return response()->json([
            'message' => 'Address deleted successfully.',
        ]);
    }
}

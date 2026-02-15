<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\DTOs\CreateAddressData;
use Modules\Customer\Http\Requests\AddressRequest;
use Modules\Customer\Services\AddressService;
use Modules\Customer\Transformers\CustomerAddressResource;
use Modules\Customer\Models\CustomerAddress;

class AddressController extends ApiController
{
    public function __construct(
        protected AddressService $addressService
    ) {
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses;
        
        return $this->apiBody([
            'addresses' => CustomerAddressResource::collection($addresses)
        ])->apiResponse();
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $data = CreateAddressData::fromRequest($request);
        $address = $this->addressService->createAddress($request->user(), $data);

        return $this->apiMessage('Address added successfully.')
            ->apiBody(['address' => new CustomerAddressResource($address)])
            ->apiCode(201)
            ->apiResponse();
    }

    public function update(AddressRequest $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('update', $address);
        
        $data = CreateAddressData::fromRequest($request); // Reuse generic DTO or create specific one
        $updatedAddress = $this->addressService->updateAddress($request->user(), $address, $data);

        return $this->apiMessage('Address updated successfully.')
            ->apiBody(['address' => new CustomerAddressResource($updatedAddress)])
            ->apiResponse();
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);
        
        $this->addressService->deleteAddress($request->user(), $address);

        return $this->apiMessage('Address deleted successfully.')
            ->apiResponse();
    }
}

<?php

namespace Modules\Customer\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Customer;
use Modules\Customer\DTOs\CreateAddressData;
use Modules\Customer\Models\CustomerAddress;
use Illuminate\Validation\ValidationException;

class AddressService
{
    public function __construct(
        protected GeoValidationService $geoService
    ) {}

    public function createAddress(Customer $customer, CreateAddressData $data): CustomerAddress
    {
        if (!$this->geoService->validate($data->country, $data->city, $data->area)) {
             throw ValidationException::withMessages(['area' => 'Invalid location combination.']);
        }

        return DB::transaction(function () use ($customer, $data) {
            if ($data->is_default) {
                $customer->addresses()->update(['is_default' => false]);
            }

            return $customer->addresses()->create([
                'label' => $data->label,
                'country' => $data->country,
                'city' => $data->city,
                'area' => $data->area,
                'street' => $data->street,
                'building' => $data->building,
                'floor' => $data->floor,
                'apartment' => $data->apartment,
                'postal_code' => $data->postal_code,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'is_default' => $data->is_default,
            ]);
        });
    }

    public function updateAddress(Customer $customer, CustomerAddress $address, CreateAddressData $data): CustomerAddress
    {
        // Ownership check should be in Policy, but good to be safe here or in Action
        if ($address->customer_id !== $customer->id) {
            abort(403);
        }
        
        if (!$this->geoService->validate($data->country, $data->city, $data->area)) {
             throw ValidationException::withMessages(['area' => 'Invalid location combination.']);
        }

        return DB::transaction(function () use ($customer, $address, $data) {
            if ($data->is_default && !$address->is_default) {
                $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'label' => $data->label,
                'country' => $data->country,
                'city' => $data->city,
                'area' => $data->area,
                'street' => $data->street,
                'building' => $data->building,
                'floor' => $data->floor,
                'apartment' => $data->apartment,
                'postal_code' => $data->postal_code,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'is_default' => $data->is_default,
            ]);
            
            return $address;
        });
    }

    public function deleteAddress(Customer $customer, CustomerAddress $address): void
    {
         if ($address->customer_id !== $customer->id) {
            abort(403);
        }
        $address->delete();
    }
}

<?php

namespace Modules\Customer\Services;

use Modules\Customer\DTOs\UpdateProfileData;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerProfile;

class ProfileService
{
    public function updateProfile(Customer $customer, UpdateProfileData $data): CustomerProfile
    {
        return $customer->profile()->updateOrCreate(
            ['customer_id' => $customer->id],
            array_filter([
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'date_of_birth' => $data->date_of_birth,
                'gender' => $data->gender?->value,
            ], fn($value) => !is_null($value))
        );
    }

    public function getProfile(Customer $customer): CustomerProfile
    {
        return $customer->profile ?? $customer->profile()->create();
    }
}

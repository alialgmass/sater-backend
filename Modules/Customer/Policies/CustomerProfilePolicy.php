<?php

namespace Modules\Customer\Policies;

use Modules\Auth\Models\Customer;
use Modules\Customer\Models\CustomerProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerProfilePolicy
{
    use HandlesAuthorization;

    public function view(Customer $customer, CustomerProfile $profile)
    {
        return $customer->id === $profile->customer_id;
    }

    public function update(Customer $customer, CustomerProfile $profile)
    {
        return $customer->id === $profile->customer_id;
    }
}

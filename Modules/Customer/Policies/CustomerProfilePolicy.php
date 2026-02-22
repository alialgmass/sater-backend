<?php

namespace Modules\Customer\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerProfile;

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

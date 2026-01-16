<?php

namespace Modules\Customer\Policies;

use Modules\Auth\Models\Customer;
use Modules\Customer\Models\CustomerAddress;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerAddressPolicy
{
    use HandlesAuthorization;

    public function view(Customer $customer, CustomerAddress $address)
    {
        return $customer->id === $address->customer_id;
    }

    public function update(Customer $customer, CustomerAddress $address)
    {
        return $customer->id === $address->customer_id;
    }

    public function delete(Customer $customer, CustomerAddress $address)
    {
        return $customer->id === $address->customer_id;
    }
}

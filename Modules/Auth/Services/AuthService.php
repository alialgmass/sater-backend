<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Modules\Auth\Actions\RegisterCustomerAction;
use Modules\Auth\DTOs\RegisterCustomerData;

class AuthService
{
    public function __construct(
        protected RegisterCustomerAction $registerCustomerAction
    ) {}

    public function registerCustomer(RegisterCustomerData $data): Customer
    {
        $customer = $this->registerCustomerAction->execute($data);

        event(new Registered($customer));

        return $customer;
    }
}

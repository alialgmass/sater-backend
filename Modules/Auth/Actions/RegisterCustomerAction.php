<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Models\Customer;
use Illuminate\Support\Facades\DB;
use Modules\Auth\DTOs\RegisterCustomerData;

class RegisterCustomerAction
{
    public function execute(RegisterCustomerData $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
                'phone' => $data->phone,
            ]);

            // No need to assign role if we are using separate model, unless we have internal roles for customers

            return $customer;
        });
    }
}

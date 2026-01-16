<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\DTOs\LoginData;

class LoginCustomerAction
{
    public function execute(LoginData $data): Customer
    {
        $customer = Customer::where('email', $data->email)->first();

        if (! $customer || ! Hash::check($data->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return $customer;
    }
}

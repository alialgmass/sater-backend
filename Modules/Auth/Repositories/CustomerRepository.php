<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\DTOs\RegisterCustomerData;
use Modules\Auth\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function create(RegisterCustomerData $data): Customer
    {
        return Customer::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'phone' => $data->phone,
        ]);
    }

    public function findByEmail(string $email): ?Customer
    {
        return Customer::where('email', $email)->first();
    }

    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }
}

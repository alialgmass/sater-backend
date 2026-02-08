<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\DTOs\RegisterCustomerData;
use Modules\Auth\Models\Customer;

interface CustomerRepositoryInterface
{
    public function create(RegisterCustomerData $data): Customer;
    public function findByEmail(string $email): ?Customer;
    public function findById(int $id): ?Customer;
}

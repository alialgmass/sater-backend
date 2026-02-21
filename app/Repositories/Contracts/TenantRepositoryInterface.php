<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;

interface TenantRepositoryInterface
{
    public function create(array $data): Tenant;

    public function findById(string $id): ?Tenant;

    public function findByEmail(string $email): ?Tenant;

    public function update(Tenant $tenant, array $data): Tenant;

    public function delete(Tenant $tenant): void;
}

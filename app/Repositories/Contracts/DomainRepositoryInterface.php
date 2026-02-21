<?php

namespace App\Repositories\Contracts;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Collection;

interface DomainRepositoryInterface
{
    public function findById(string $id): ?Domain;

    public function findByName(string $name): ?Domain;

    public function existsByName(string $name): bool;

    public function getForTenant(Tenant $tenant): Collection;

    public function getSubdomainForTenant(Tenant $tenant): ?Domain;

    public function create(array $data): Domain;

    public function update(Domain $domain, array $data): Domain;

    public function delete(Domain $domain): void;

    public function unsetOtherPrimaries(Tenant $tenant, Domain $except): void;
}

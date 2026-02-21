<?php

namespace App\Repositories;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Contracts\DomainRepositoryInterface;
use Illuminate\Support\Collection;

class DomainRepository implements DomainRepositoryInterface
{
    public function findById(string $id): ?Domain
    {
        return Domain::find($id);
    }

    public function findByName(string $name): ?Domain
    {
        return Domain::where('domain', $name)->first();
    }

    public function existsByName(string $name): bool
    {
        return Domain::where('domain', $name)->exists();
    }

    public function getForTenant(Tenant $tenant): Collection
    {
        return $tenant->domains()
            ->orderBy('is_primary', 'desc')
            ->orderBy('domain')
            ->get();
    }

    public function getSubdomainForTenant(Tenant $tenant): ?Domain
    {
        return $tenant->domains()->where('type', 'subdomain')->first();
    }

    public function create(array $data): Domain
    {
        return Domain::create($data);
    }

    public function update(Domain $domain, array $data): Domain
    {
        $domain->update($data);

        return $domain->refresh();
    }

    public function delete(Domain $domain): void
    {
        $domain->delete();
    }

    public function unsetOtherPrimaries(Tenant $tenant, Domain $except): void
    {
        $tenant->domains()
            ->where('id', '!=', $except->id)
            ->update(['is_primary' => false]);
    }
}

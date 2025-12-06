<?php

namespace App\Support\Classes;

use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByPathException;
use Stancl\Tenancy\Resolvers\Contracts\CachedTenantResolver;

class MultiTenantResolver extends CachedTenantResolver
{
    public static string $tenantParameterName = 'tenant';

    /** @var bool */
    public static $shouldCache = false;

    /** @var int */
    public static $cacheTTL = 3600; // seconds

    /** @var string|null */
    public static $cacheStore = null; // default

    /**
     * @throws TenantCouldNotBeIdentifiedByPathException
     */
    public function resolveWithoutCache(...$args): Tenant
    {
        if ($tenant = tenancy()->find(user('tenant_id'))) {
            return $tenant;
        }

        throw new TenantCouldNotBeIdentifiedByPathException(...$args);
    }

    public function getArgsForTenant(Tenant $tenant): array
    {
        return [
            [$tenant->id],
        ];
    }
}

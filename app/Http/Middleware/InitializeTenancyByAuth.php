<?php

namespace App\Http\Middleware;

use App\Support\Classes\MultiTenantResolver;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\IdentificationMiddleware;
use Stancl\Tenancy\Tenancy;

/**
 * Class CustomTenantMiddleware
 */
class InitializeTenancyByAuth extends IdentificationMiddleware
{
    /** @var callable|null */
    public static $onFail;

    /** @var Tenancy */
    protected $tenancy;

    /** @var MultiTenantResolver */
    protected $resolver;

    public function __construct(Tenancy $tenancy, MultiTenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tenant = user('tenant_id');

        if ($tenant) {
            return $this->initializeTenancy(
                $request,
                $next,
                $tenant
            );
        }

        return $next($request);
    }
}

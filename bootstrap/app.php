<?php

use App\Exceptions\ApiException\ApiException;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TenantSubscriptionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Middleware\CustomerMustVerified;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Tenant middleware group
        $middleware->group('tenant', [
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            TenantSubscriptionMiddleware::class,
        ]);

        // Alias for tenant subscription middleware
        $middleware->alias([
            'tenant.subscription' => TenantSubscriptionMiddleware::class,
            'phone.verified' => CustomerMustVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ApiException $e) {
          return  $e->toResponse();
        });
    })
    ->withEvents(discover: [
        __DIR__.'/../Modules/*/Events',
        __DIR__.'/../Modules/*/Listeners',

    ])

    ->create();

<?php

namespace Modules\Customer\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Customer\Models\CustomerProfile;
use Modules\Customer\Policies\CustomerProfilePolicy;
use Modules\Customer\Models\CustomerAddress;
use Modules\Customer\Policies\CustomerAddressPolicy;
use Illuminate\Support\Facades\Gate;

class CustomerServiceProvider extends ServiceProvider
{
    protected $policies = [
        CustomerProfile::class => CustomerProfilePolicy::class,
        CustomerAddress::class => CustomerAddressPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function registerPolicies(): void
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('Modules\Customer\Http\Controllers\Api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}

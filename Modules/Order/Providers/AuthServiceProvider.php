<?php

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Order\Models\Order;
use Modules\Order\Models\VendorOrder;
use Modules\Order\Policies\OrderPolicy;
use Modules\Order\Policies\VendorOrderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        VendorOrder::class => VendorOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}

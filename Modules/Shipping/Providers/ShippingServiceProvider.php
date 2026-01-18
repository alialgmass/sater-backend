<?php

namespace Modules\Shipping\Providers;

use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
<?php

namespace Modules\Checkout\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Checkout\Repositories\CheckoutSessionRepository;
use Modules\Checkout\Repositories\Contracts\CheckoutSessionRepositoryInterface;

class CheckoutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CheckoutSessionRepositoryInterface::class,
            CheckoutSessionRepository::class,
        );
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('Modules\Checkout\Http\Controllers\Api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}


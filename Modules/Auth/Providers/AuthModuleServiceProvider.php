<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthModuleServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();

        $this->loadFactoriesFrom(__DIR__ . '/../database/factories');
    }

    public function register(): void
    {
        $this->app->bind(
            \Modules\Auth\Repositories\CustomerRepositoryInterface::class,
            \Modules\Auth\Repositories\CustomerRepository::class
        );

    }

    protected function registerRoutes(): void
    {
        Route::prefix('api/auth')
            ->middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    protected function registerMigrations(): void
    {
        // Migrations are currently in database/migrations
        // If we move them back to Modules/Auth/Database/Migrations:
        // $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
    protected function re()
    {

    }
}

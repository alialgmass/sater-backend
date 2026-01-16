<?php

namespace Modules\Cart\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Policies\CartItemPolicy;
use Modules\Cart\Models\WishlistItem;
use Modules\Cart\Policies\WishlistItemPolicy;

class CartServiceProvider extends ServiceProvider
{
    protected $policies = [
        CartItem::class => CartItemPolicy::class,
        WishlistItem::class => WishlistItemPolicy::class,
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
            ->namespace('Modules\Cart\Http\Controllers\Api')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}

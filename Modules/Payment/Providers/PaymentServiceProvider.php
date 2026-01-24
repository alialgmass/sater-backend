<?php

namespace Modules\Payment\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Services\PaymentInitiationService;
use Modules\Payment\Services\PaymentVerificationService;
use Modules\Payment\Services\PaymentWebhookService;
use Modules\Payment\Services\ReceiptService;

class PaymentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Payment';

    protected string $moduleNameLower = 'payment';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(module_path($this->moduleName, '/Routes/api.php'));
    }

    /**
     * Register factories.
     */
    protected function registerFactories(): void
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            /*foreach (glob(module_path($this->moduleName, '/Database/factories/*.php')) as $factory) {
                $this->app->make(Factory::class)->load($factory);
            }*/
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
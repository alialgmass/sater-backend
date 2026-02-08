<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Product\Services\Search\FilterService;
use Modules\Product\Services\Search\ProductSearchQueryBuilder;
use Modules\Product\Services\Search\SearchHistoryService;
use Modules\Product\Services\Search\SearchService;
use Modules\Product\Services\Search\SearchSuggestionService;
use Modules\Product\Services\Search\SortService;

/**
 * Search Service Provider
 *
 * Registers all search-related services in the container
 * This enables dependency injection throughout the application
 */
class SearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register core services
        $this->app->singleton(FilterService::class, function ($app) {
            return new FilterService();
        });

        $this->app->singleton(SortService::class, function ($app) {
            return new SortService();
        });

        $this->app->singleton(ProductSearchQueryBuilder::class, function ($app) {
            return new ProductSearchQueryBuilder(
                $app->make(FilterService::class),
                $app->make(SortService::class)
            );
        });

        $this->app->singleton(SearchService::class, function ($app) {
            return new SearchService(
                $app->make(ProductSearchQueryBuilder::class),
                $app->make(FilterService::class),
                $app->make(SortService::class)
            );
        });

        $this->app->singleton(SearchHistoryService::class, function ($app) {
            return new SearchHistoryService();
        });

        $this->app->singleton(SearchSuggestionService::class, function ($app) {
            return new SearchSuggestionService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Schedule search history pruning
        // This would be called from a scheduled task
        // See: app/Console/Kernel.php
    }
}

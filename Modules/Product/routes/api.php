<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Api\ProductController;
use Modules\Product\Http\Controllers\Api\SearchController;
use Modules\Product\Http\Controllers\Api\ColorController;
use Modules\Product\Http\Controllers\Api\SizeController;
use Modules\Product\Http\Controllers\Api\TagController;

Route::middleware([])->prefix('v1')->group(function () {
    // Products CRUD
    Route::apiResource('products', ProductController::class)
        ->only('index', 'show')
        ->names('product');

    // Attributes
    Route::apiResource('colors', ColorController::class)->only('index');
    Route::apiResource('sizes', SizeController::class)->only('index');
    Route::apiResource('tags', TagController::class)->only('index');

    // Search endpoints (public access)
    Route::prefix('search')->group(function () {
        Route::get('products', [SearchController::class, 'search'])->name('search.products');
        Route::get('autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
        Route::get('cursor', [SearchController::class, 'searchCursor'])->name('search.cursor');
    });

    // Search history (authenticated only)
    Route::middleware('auth:sanctum')->prefix('search')->group(function () {
        Route::get('history', [SearchController::class, 'history'])->name('search.history');
        Route::delete('history', [SearchController::class, 'clearHistory'])->name('search.history.clear');
        Route::delete('history/{id}', [SearchController::class, 'deleteHistory'])->name('search.history.delete');
    });
});

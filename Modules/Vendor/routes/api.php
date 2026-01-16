<?php

use Illuminate\Support\Facades\Route;
use Modules\Vendor\Http\Controllers\Api\VendorAuthController;
use Modules\Vendor\Http\Controllers\Api\VendorController;
use Modules\Vendor\Http\Controllers\Api\VendorSearchController;

Route::middleware([])->prefix('v1')->group(function () {
    Route::apiResource('vendors', VendorController::class)
    ->only('index')
    ->names('vendor');

    // Vendor store search
    Route::get('vendors/{vendor_id}/search', [VendorSearchController::class, 'search'])->name('vendor.search');
});

Route::prefix('vendors')->group(function () {
    // Public routes
    Route::post('/register', [VendorAuthController::class, 'register']);
    Route::get('/check-slug/{slug}', [VendorAuthController::class, 'checkSlug']);
    // Route::get('/{slug}', [VendorAuthController::class, 'show']);
    // Route::get('/', [VendorAuthController::class, 'index']);
    // Route::get('/search', [VendorAuthController::class, 'search']);
});

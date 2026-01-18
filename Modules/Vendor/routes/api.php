<?php

use Illuminate\Support\Facades\Route;
use Modules\Vendor\Http\Controllers\Api\VendorAuthController;
use Modules\Vendor\Http\Controllers\Api\VendorController;
use Modules\Vendor\Http\Controllers\Api\VendorSearchController;
use Modules\Vendor\Http\Controllers\Api\VendorShippingController;

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

// Vendor shipping routes
Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor/shipping')->name('vendor.shipping.')->group(function () {
    Route::get('/', [VendorShippingController::class, 'index'])->name('index');
    Route::get('/{id}', [VendorShippingController::class, 'show'])->name('show');
    Route::put('/{id}/status', [VendorShippingController::class, 'updateStatus'])->name('update.status');
    Route::put('/{id}/tracking', [VendorShippingController::class, 'addTrackingInfo'])->name('add.tracking');
    Route::get('/{id}/attempts', [VendorShippingController::class, 'deliveryAttempts'])->name('attempts');
    Route::put('/{id}/cod-delivered', [VendorShippingController::class, 'markCodAsDelivered'])->name('cod.delivered');
});

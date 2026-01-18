<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminShippingController;

// Admin shipping routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/shipping')->name('admin.shipping.')->group(function () {
    // Shipping zones management
    Route::get('/zones', [AdminShippingController::class, 'indexZones'])->name('zones.index');
    Route::post('/zones', [AdminShippingController::class, 'storeZone'])->name('zones.store');
    Route::put('/zones/{id}', [AdminShippingController::class, 'updateZone'])->name('zones.update');
    Route::delete('/zones/{id}', [AdminShippingController::class, 'destroyZone'])->name('zones.destroy');
    
    // Zone locations
    Route::post('/zones/{zoneId}/locations', [AdminShippingController::class, 'addZoneLocation'])->name('zones.locations.store');
    
    // Vendor shipping methods
    Route::get('/vendor-methods', [AdminShippingController::class, 'indexVendorMethods'])->name('vendor-methods.index');
    Route::post('/vendor-methods', [AdminShippingController::class, 'storeVendorMethod'])->name('vendor-methods.store');
    Route::put('/vendor-methods/{id}', [AdminShippingController::class, 'updateVendorMethod'])->name('vendor-methods.update');
    
    // Courier integrations
    Route::get('/couriers', [AdminShippingController::class, 'courierIntegrationIndex'])->name('couriers.index');
    Route::post('/couriers/{courierId}/configure', [AdminShippingController::class, 'configureCourier'])->name('couriers.configure');
});
<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\Api\OrderController;
use Modules\Order\Http\Controllers\Api\VendorOrderController;

// Customer order routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('orders', OrderController::class)->names('order');
});

// Vendor-specific order routes
Route::middleware(['auth:sanctum', 'role:vendor'])->prefix('vendor/orders')->name('vendor.orders.')->group(function () {
    Route::get('/', [VendorOrderController::class, 'index'])->name('index');
    Route::get('/{vendor_order_number}', [VendorOrderController::class, 'show'])->name('show');
    Route::post('/{vendor_order_number}/status', [VendorOrderController::class, 'updateStatus'])->name('update.status');
    Route::post('/{vendor_order_number}/shipping', [VendorOrderController::class, 'addShippingInfo'])->name('add.shipping');
    Route::post('/bulk', [VendorOrderController::class, 'bulkAction'])->name('bulk.action');
    Route::get('/{vendor_order_number}/packing-slip', [VendorOrderController::class, 'packingSlip'])->name('packing-slip');
    Route::get('/export', [VendorOrderController::class, 'export'])->name('export');
});
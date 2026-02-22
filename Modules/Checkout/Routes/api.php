<?php

use Illuminate\Support\Facades\Route;
use Modules\Checkout\Http\Controllers\Api\CheckoutController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Checkout\Http\Controllers\Api\CheckoutController;

Route::prefix('checkout')->middleware('auth:sanctum')->group(function () {
    Route::post('/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::post('/address', [CheckoutController::class, 'selectAddress'])->name('checkout.address');
    Route::post('/shipping', [CheckoutController::class, 'selectShipping'])->name('checkout.shipping');
    Route::post('/payment', [CheckoutController::class, 'selectPayment'])->name('checkout.payment');
    Route::post('/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::get('/summary', [CheckoutController::class, 'summary'])->name('checkout.summary');
    Route::post('/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
});

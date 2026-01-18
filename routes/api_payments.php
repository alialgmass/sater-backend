<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Payment\PaymentController;

// Payment routes
Route::prefix('payments')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiate']);
    Route::post('/verify', [PaymentController::class, 'verify']);
    Route::get('/orders/{orderNumber}/status', [PaymentController::class, 'getStatusByOrderNumber']);
    
    // Webhook route - accessible without authentication
    Route::post('/webhook/{gateway}', [PaymentController::class, 'webhook']);
    
    // Callback routes
    Route::get('/success', [PaymentController::class, 'successCallback']);
    Route::get('/cancel', [PaymentController::class, 'cancelCallback']);
});
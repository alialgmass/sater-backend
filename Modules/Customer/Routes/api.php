<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\Api\ProfileController;
use Modules\Customer\Http\Controllers\Api\AddressController;

Route::middleware(['auth:api_customers', 'verified'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    
    Route::get('addresses', [AddressController::class, 'index']);
    Route::post('addresses', [AddressController::class, 'store']);
    Route::put('addresses/{address}', [AddressController::class, 'update']);
    Route::delete('addresses/{address}', [AddressController::class, 'destroy']);
    
    Route::post('account/delete', [ProfileController::class, 'deleteAccount']);
});

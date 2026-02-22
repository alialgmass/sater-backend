<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('phone.verified');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('verify-otp', [AuthController::class, 'verify']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

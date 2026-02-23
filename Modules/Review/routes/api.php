<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;

Route::prefix('v1')->group(function () {
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews', [ReviewController::class, 'store'])
        ->middleware('auth:api_customers')
        ->name('reviews.store');
});

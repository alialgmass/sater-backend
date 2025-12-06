<?php

use Illuminate\Support\Facades\Route;
use Modules\Stock\Http\Controllers\StockController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('stocks', StockController::class)->names('stock');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Stock\Http\Controllers\StockController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('stocks', StockController::class)->names('stock');
});

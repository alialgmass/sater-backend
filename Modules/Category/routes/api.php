<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\Api\CategoryController;

Route::middleware([])->prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class)
        ->only('index')
        ->names('category');
});

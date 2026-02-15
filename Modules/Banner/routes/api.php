<?php

use Illuminate\Support\Facades\Route;
use Modules\Banner\Http\Controllers\Api\BannerController;

Route::middleware([])->prefix('v1')->group(function () {
    Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('banners/active', [BannerController::class, 'active'])->name('banners.active');
});

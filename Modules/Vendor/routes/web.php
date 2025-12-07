<?php

use Illuminate\Support\Facades\Route;
use Modules\Vendor\Http\Controllers\VendorController;

Route::middleware([])->group(function () {
    Route::resource('vendors', VendorController::class)->names('vendor');
});

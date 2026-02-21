<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenancyServiceProvider.
|
| All routes in this file are automatically scoped to the current tenant.
| The tenant is identified by the domain/subdomain via middleware.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Tenant Dashboard
    Route::get('/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    // Example: Tenant Products (when implemented)
    // Route::resource('products', ProductController::class);
    
    // Example: Tenant Orders (when implemented)
    // Route::resource('orders', OrderController::class);
    
    // Example: Tenant Settings
    // Route::get('/settings', [TenantSettingsController::class, 'index'])->name('tenant.settings');

    // Home
    Route::get('/', function () {
        return view('tenant.welcome');
    })->name('tenant.home');
});

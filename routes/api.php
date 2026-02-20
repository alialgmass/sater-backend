<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Public routes (no authentication required)
    
    // Tenant Registration
    Route::post('/tenants/register', [\App\Http\Controllers\TenantRegistrationController::class, 'register'])
        ->name('api.tenants.register');
    
    Route::get('/tenants/verify/{token}', [\App\Http\Controllers\TenantRegistrationController::class, 'verify'])
        ->name('api.tenants.verify');
    
    Route::post('/tenants/resend-verification', [\App\Http\Controllers\TenantRegistrationController::class, 'resendVerification'])
        ->name('api.tenants.resend-verification');
    
    // Subscription Plans (public for selection)
    Route::get('/subscription-plans', [\App\Http\Controllers\TenantRegistrationController::class, 'listPlans'])
        ->name('api.subscription-plans.index');
    
    // Tenant Subscription (after email verification)
    Route::post('/tenants/{tenantId}/subscribe', [\App\Http\Controllers\TenantRegistrationController::class, 'subscribe'])
        ->name('api.tenants.subscribe');
    
    // Domain Management (would be protected with auth:sanctum in production)
    Route::prefix('/tenants/{tenantId}/domains')->group(function () {
        Route::get('/', [\App\Http\Controllers\DomainManagementController::class, 'index'])
            ->name('api.domains.index');
        Route::post('/', [\App\Http\Controllers\DomainManagementController::class, 'store'])
            ->name('api.domains.store');
        Route::put('/{domainId}/verify', [\App\Http\Controllers\DomainManagementController::class, 'verify'])
            ->name('api.domains.verify');
        Route::put('/{domainId}/primary', [\App\Http\Controllers\DomainManagementController::class, 'setPrimary'])
            ->name('api.domains.primary');
        Route::delete('/{domainId}', [\App\Http\Controllers\DomainManagementController::class, 'destroy'])
            ->name('api.domains.destroy');
    });
    
    // Subdomain Change
    Route::put('/tenants/{tenantId}/subdomain', [\App\Http\Controllers\DomainManagementController::class, 'changeSubdomain'])
        ->name('api.tenants.subdomain.change');
    
    // Protected API routes would go here (with Sanctum middleware)
    // Route::middleware(['auth:sanctum'])->group(function () {
    //     // Authenticated routes
    // });
});

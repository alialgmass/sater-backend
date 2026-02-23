<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\Api\CartController;
use Modules\Cart\Http\Controllers\Api\WishlistController;

// Cart routes - support both guest and authenticated users
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/', [CartController::class, 'clear'])->name('cart.clear');
    
    // Unified item CRUD (handles guest/auth internally)
    Route::put('/items/{id}', [CartController::class, 'updateItem'])->name('cart.items.update');
    Route::delete('/items/{id}', [CartController::class, 'removeItem'])->name('cart.items.remove');
    
    // Authenticated only
    Route::middleware(['auth:api_customers'])->group(function () {
        Route::post('/items/{id}/save-for-later', [CartController::class, 'saveForLater'])->name('cart.items.save');
    });
});

// Wishlist routes - authenticated only
Route::middleware(['auth:api_customers'])->prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/share', [WishlistController::class, 'share'])->name('wishlist.share');
    Route::post('/{product}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.move');
});

// Public wishlist sharing
Route::get('/wishlist/shared/{token}', [WishlistController::class, 'viewShared'])->name('wishlist.shared');

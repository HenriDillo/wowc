<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController as CheckoutOrderController;
use App\Http\Controllers\OrderApiController;
use App\Http\Controllers\AddressApiController;

// Public API endpoints (consider adding auth or sanctum later)
Route::prefix('v1')->group(function () {
    // Cart
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::post('/cart/{itemId}/remove', [CartController::class, 'removeFromCart']);
    Route::post('/cart/{itemId}/quantity', [CartController::class, 'updateQuantity']);
    Route::get('/cart', [CartController::class, 'showCart']);

    // Checkout
    Route::post('/checkout', [CheckoutOrderController::class, 'store']);
    Route::get('/orders', [CheckoutOrderController::class, 'index']);
    Route::get('/orders/{id}', [CheckoutOrderController::class, 'show']);

    // User relations
    Route::get('/users/{user}/orders', [OrderApiController::class, 'index']);
    Route::post('/users/{user}/orders', [OrderApiController::class, 'store']);

    Route::get('/users/{user}/addresses', [AddressApiController::class, 'index']);
    Route::post('/users/{user}/addresses', [AddressApiController::class, 'store']);
});



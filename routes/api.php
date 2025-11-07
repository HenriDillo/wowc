<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController as CheckoutOrderController;
use App\Http\Controllers\OrderApiController;
use App\Http\Controllers\AddressApiController;

// Public API endpoints (consider adding auth or sanctum later)
Route::prefix('v1')->group(function () {
    // Cart
    // Cart endpoints are session-enabled and defined in web routes (api/v1 with web middleware).
    // They were removed from this file to avoid duplicate route definitions that bypass the web
    // middleware (which provides session cookies). See routes/web.php for the cart endpoints.

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



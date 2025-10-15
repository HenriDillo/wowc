<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Employee\MaterialController;
use App\Http\Controllers\Employee\ItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController as CartPageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Public pages
Route::prefix('')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{item}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/cart', [CartPageController::class, 'page'])->name('cart.page');
});

// Checkout (authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'page'])->name('checkout.page');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Customer orders (authenticated)
Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [CustomerOrderController::class, 'show'])->name('orders.show');
});

// Role-based dashboard
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->status === 'blocked') {
        Auth::logout();
        return redirect('/')->with('blocked', 'Your account has been blocked. Contact admin.');
    }
    if ($user->role === 'admin') {
        return redirect()->route('admin.users');
    } elseif ($user->role === 'employee') {
        // Employee dashboard metrics
        $totalProducts = \App\Models\Item::count();
        $lowStockCount = \App\Models\Item::where('stock', '<', 5)->count();
        $totalRawMaterials = \App\Models\Material::count();
        $totalItems = $totalProducts;
        $totalItemStock = (int) (\App\Models\Item::sum('stock') ?? 0);
        $totalMaterialStock = (int) (\App\Models\Material::sum('stock') ?? 0);

        $totalOrders = \App\Models\Order::count();
        $ordersPending = \App\Models\Order::where('status', 'pending')->count();
        $ordersCompleted = \App\Models\Order::where('status', 'completed')->count();
        $ordersCancelled = \App\Models\Order::where('status', 'cancelled')->count();

        // Recent activity
        $recentMaterials = \App\Models\Material::latest()->take(5)->get();
        $recentItems = \App\Models\Item::latest()->take(5)->get();
        $recentOrders = \App\Models\Order::latest()->take(5)->get();

        return view('dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'totalRawMaterials',
            'totalItems',
            'totalItemStock',
            'totalMaterialStock',
            'totalOrders',
            'ordersPending',
            'ordersCompleted',
            'ordersCancelled',
            'recentMaterials',
            'recentItems',
            'recentOrders'
        ));
    } else {
        $products = \App\Models\Item::query()
            ->where('visible', true)
            ->orderByDesc('id')
            ->take(8)
            ->get();
        return view('customer', compact('products')); // customer landing page with featured products
    }
})->middleware(['auth'])->name('dashboard');

// Profile routes (authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Employee routes (authenticated, prefixed and named)
Route::middleware('auth')->prefix('employee')->name('employee.')->group(function () {
    // Materials
    Route::get('/raw-materials', [MaterialController::class, 'index'])->name('raw-materials');
    Route::post('/raw-materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::put('/raw-materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::patch('/raw-materials/{material}/hide', [MaterialController::class, 'hide'])->name('materials.hide');
    Route::patch('/raw-materials/{material}/unhide', [MaterialController::class, 'unhide'])->name('materials.unhide');

    // Items
    Route::get('/items', [ItemController::class, 'index'])->name('items');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::patch('/items/{item}/toggle', [ItemController::class, 'toggleVisibility'])->name('items.toggle');
    Route::delete('/items/photos/{photo}', [ItemController::class, 'destroyPhoto'])->name('items.photos.destroy');

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Employee\OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [\App\Http\Controllers\Employee\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{id}', [\App\Http\Controllers\Employee\OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{id}', [\App\Http\Controllers\Employee\OrderController::class, 'destroy'])->name('orders.destroy');
});

// Admin routes (authenticated, prefixed and named)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.updateRole');
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/items/{item}/add', [StockController::class, 'addItemStock'])->name('stock.items.add');
    Route::post('/stock/materials/{material}/add', [StockController::class, 'addMaterialStock'])->name('stock.materials.add');
    Route::post('/stock/materials/{material}/reduce', [StockController::class, 'reduceMaterialStock'])->name('stock.materials.reduce');
});

require __DIR__.'/auth.php';

// Session-enabled API endpoints (cart) using web middleware
Route::prefix('api/v1')->middleware('web')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::post('/cart/{itemId}/remove', [CartController::class, 'removeFromCart']);
    Route::post('/cart/{itemId}/quantity', [CartController::class, 'updateQuantity']);
    Route::get('/cart', [CartController::class, 'showCart']);
});

// Fallback media route when public/storage symlink isn't available
Route::get('/media/{path}', function ($path) {
    $cleanPath = ltrim($path, '/');
    if (!Storage::disk('public')->exists($cleanPath)) {
        abort(404);
    }
    $mime = Storage::disk('public')->mimeType($cleanPath) ?? 'application/octet-stream';
    return response(Storage::disk('public')->get($cleanPath), 200)->header('Content-Type', $mime);
})->where('path', '.*');

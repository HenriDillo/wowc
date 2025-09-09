<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Employee\MaterialController;
use App\Http\Controllers\Employee\ItemController;

Route::get('/', function () {
    return view('welcome');
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
        // Simple stats for dashboard
        $totalProducts = \App\Models\Item::count();
        $lowStockCount = \App\Models\Item::where('stock', '<', 5)->count();
        $ordersToday = 0; // Placeholder until orders feature exists
        $stockInToday = 0; // Placeholder until stock-in feature exists
        return view('dashboard', compact('totalProducts', 'lowStockCount', 'ordersToday', 'stockInToday'));
    } else {
        return view('customer'); // customer landing page
    }
})->middleware(['auth'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Employee pages (database-backed)
    Route::get('/employee/raw-materials', [MaterialController::class, 'index'])->name('employee.raw-materials');
    Route::post('/employee/raw-materials', [MaterialController::class, 'store'])->name('employee.materials.store');
    Route::put('/employee/raw-materials/{material}', [MaterialController::class, 'update'])->name('employee.materials.update');
    Route::patch('/employee/raw-materials/{material}/hide', [MaterialController::class, 'hide'])->name('employee.materials.hide');
Route::patch('/employee/raw-materials/{material}/unhide', [MaterialController::class, 'unhide'])->name('employee.materials.unhide');

    Route::get('/employee/items', [ItemController::class, 'index'])->name('employee.items');
    Route::post('/employee/items', [ItemController::class, 'store'])->name('employee.items.store');
    Route::put('/employee/items/{item}', [ItemController::class, 'update'])->name('employee.items.update');
    Route::patch('/employee/items/{item}/toggle', [ItemController::class, 'toggleVisibility'])->name('employee.items.toggle');
});

// Admin routes (only admins allowed)
Route::middleware(['auth'])->group(function() {
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::put('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::patch('/admin/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
});

require __DIR__.'/auth.php';

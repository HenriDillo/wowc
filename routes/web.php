<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminUserController;

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
        return view('dashboard'); // employee landing page
    } else {
        return view('customer'); // customer landing page
    }
})->middleware(['auth'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes (only admins allowed)
Route::middleware(['auth'])->group(function() {
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::put('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::patch('/admin/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
});

require __DIR__.'/auth.php';

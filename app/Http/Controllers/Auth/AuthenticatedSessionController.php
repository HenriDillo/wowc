<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    // Show login form
    public function create()
    {
        return view('auth.login');
    }

    // Handle login POST
    public function store(LoginRequest $request)
{
    $request->authenticate();
    $user = Auth::user();

    // Check if user is blocked
    if ($user->status === 'blocked') {
        Auth::logout();
        return redirect('/')->with('blocked', 'Your account has been blocked. Contact admin.');
    }

    $request->session()->regenerate();

    // Redirect based on role
    if ($user->role === 'admin') {
        return redirect()->route('admin.users');
    } elseif ($user->role === 'employee') {
        return redirect()->route('dashboard'); // employee route
    } else {
        return redirect()->route('dashboard'); // customer route
    }
}

    // Handle logout
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    
    
}

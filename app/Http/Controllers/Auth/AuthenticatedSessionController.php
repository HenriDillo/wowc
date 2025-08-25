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

        $request->session()->regenerate();

        // Redirect based on role
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.users');
        } elseif ($user->role === 'employee') {
            return redirect()->route('dashboard'); // Or your employee route
        } else { // customer
            return redirect()->route('dashboard'); // Or your customer route
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

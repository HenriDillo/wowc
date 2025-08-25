<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminUserController extends Controller
{
    // Show all users
    public function index()
    {
        $user = Auth::user();

        // Only admin can access
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $users = User::all();
        return view('admin.users', compact('users'));
    }

    // Update user role
    public function updateRole(Request $request, User $user)
    {
        $authUser = Auth::user();

        // Only admin can update roles
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'role' => 'required|in:admin,employee,customer'
        ]);

        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'Role updated!');
    }

    // Toggle block/unblock status
    public function toggleStatus(User $user)
    {
        $authUser = Auth::user();

        // Only admin can block/unblock users
        if (!$authUser || $authUser->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Assume status column is 'active' or 'blocked'
        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();

        return redirect()->back()->with('success', 'User status updated!');
    }
}

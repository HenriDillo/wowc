<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Display all users (admin only).
     */
    public function index()
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $users = User::all();
        return view('admin.users', compact('users'));
    }

    /**
     * Update a user's role.
     */
    public function updateRole(Request $request, User $user)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'role' => 'required|in:admin,employee,customer',
        ]);

        // Prevent changing your own role
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot change your own role.'], 403);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Role updated successfully']);
    }

    /**
     * Toggle a user's active/blocked status.
     */
    public function toggleStatus(User $user)
{
    if (!Auth::user() || Auth::user()->role !== 'admin') {
        return redirect()->back()->with('error', 'Unauthorized');
    }

    // Prevent admin from blocking themselves
    if (Auth::id() === $user->id) {
        return redirect()->back()->with('error', 'You cannot block yourself.');
    }

    $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
    $user->save();

    return response()->json(['message' => 'User status updated successfully', 'status' => $user->status]);
}

}

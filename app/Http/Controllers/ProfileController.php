<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->loadMissing('address');
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        // Combine first/last name if provided
        if (!empty($validated['first_name']) || !empty($validated['last_name'])) {
            $full = trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''));
            if ($full !== '') {
                $user->name = $full;
            }
        } elseif (!empty($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (!empty($validated['email']) && $validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        $user->save();

        // Update or create a primary address if address payload present
        $addr = $validated['address'] ?? null;
        if (is_array($addr)) {
            $user->address()->updateOrCreate([], [
                'user_id' => $user->id,
                'type' => 'shipping',
                'address_line' => $addr['address_line'] ?? null,
                'city' => $addr['city'] ?? null,
                'province' => $addr['province'] ?? null,
                'postal_code' => $addr['postal_code'] ?? null,
                'phone_number' => $addr['phone_number'] ?? null,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

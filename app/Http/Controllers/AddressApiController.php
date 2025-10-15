<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;

class AddressApiController extends Controller
{
    public function index(User $user)
    {
        return response()->json($user->addresses()->latest()->get());
    }

    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'type' => 'required|in:shipping,billing',
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:120',
            'province' => 'required|string|max:120',
            'postal_code' => 'required|string|max:20',
            'phone_number' => 'required|string|max:30',
        ]);

        $address = new Address($validated);
        $address->user()->associate($user);
        $address->save();

        return response()->json($address->fresh(), 201);
    }
}



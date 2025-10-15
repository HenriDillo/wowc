<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Address;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Check if admin already exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]);

            Address::create([
                'user_id' => $user->id,
                'type' => 'shipping',
                'address_line' => '123 Main St',
                'city' => 'Anytown',
                'province' => 'Metro',
                'postal_code' => '1000',
                'phone_number' => '1234567890',
            ]);
        }
    }
}

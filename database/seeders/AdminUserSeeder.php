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
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]);

            Address::create([
                'user_id' => $user->id,
                'type' => 'shipping',
                'address_line' => '123 Ayala Avenue, Makati Business District',
                'city' => 'Makati',
                'province' => 'Metro Manila',
                'postal_code' => '1226',
                'phone_number' => '+639171234567',
            ]);
        }
    }
}

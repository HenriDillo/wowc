<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple employees
        $u1 = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'), // default password
            'role' => 'employee',
            'status' => 'active', // make sure you have added the status column
        ]);
        Address::create([
            'user_id' => $u1->id,
            'type' => 'shipping',
            'address_line' => '123 Main St',
            'city' => 'Anytown',
            'province' => 'Metro',
            'postal_code' => '1000',
            'phone_number' => '1234567890',
        ]);

        $u2 = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'status' => 'active',
        ]);
        Address::create([
            'user_id' => $u2->id,
            'type' => 'shipping',
            'address_line' => '456 Second St',
            'city' => 'Anytown',
            'province' => 'Metro',
            'postal_code' => '1001',
            'phone_number' => '1234567890',
        ]);

        // Optionally, create more employees using a factory
        User::factory(3)->create([
            'role' => 'employee',
            'status' => 'active',
        ])->each(function (User $user) {
            Address::create([
                'user_id' => $user->id,
                'type' => 'shipping',
                'address_line' => 'Sample Address',
                'city' => 'City',
                'province' => 'Province',
                'postal_code' => '1002',
                'phone_number' => '1234567890',
            ]);
        });
    }
}

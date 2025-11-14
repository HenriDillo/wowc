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
            'address_line' => '456 Ortigas Avenue, Quezon City',
            'city' => 'Quezon City',
            'province' => 'Metro Manila',
            'postal_code' => '1605',
            'phone_number' => '+639175551234',
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
            'address_line' => '789 EDSA, Mandaluyong City',
            'city' => 'Mandaluyong',
            'province' => 'Metro Manila',
            'postal_code' => '1555',
            'phone_number' => '+639178765432',
        ]);

        // Optionally, create more employees using a factory
        $cities = ['Pasig', 'Taguig', 'Caloocan'];
        $provinces = ['Rizal', 'Rizal', 'Metro Manila'];
        $postalCodes = ['1670', '1634', '1400'];
        $streets = ['Bonifacio Global City', 'Podium', 'North Avenue'];
        $index = 0;
        
        User::factory(3)->create([
            'role' => 'employee',
            'status' => 'active',
        ])->each(function (User $user) use ($cities, $provinces, $postalCodes, $streets, &$index) {
            $phone = '+6391' . str_pad($index, 8, '0', STR_PAD_LEFT);
            Address::create([
                'user_id' => $user->id,
                'type' => 'shipping',
                'address_line' => $streets[$index % count($streets)] . ', ' . $cities[$index % count($cities)],
                'city' => $cities[$index % count($cities)],
                'province' => $provinces[$index % count($provinces)],
                'postal_code' => $postalCodes[$index % count($postalCodes)],
                'phone_number' => $phone,
            ]);
            $index++;
        });
    }
}

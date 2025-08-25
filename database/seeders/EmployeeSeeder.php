<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple employees
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'), // default password
            'role' => 'employee',
            'status' => 'active', // make sure you have added the status column
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        // Optionally, create more employees using a factory
        User::factory(3)->create([
            'role' => 'employee',
            'status' => 'active',
        ]);
    }
}

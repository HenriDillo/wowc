<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['name' => 'Abaca Fiber', 'quantity' => 25, 'unit' => 'kg'],
            ['name' => 'Rattan Strips', 'quantity' => 50, 'unit' => 'bundle'],
            ['name' => 'Bamboo Sticks', 'quantity' => 120, 'unit' => 'pcs'],
            ['name' => 'Dye (Brown)', 'quantity' => 10, 'unit' => 'liters'],
            ['name' => 'Glue', 'quantity' => 30, 'unit' => 'bottles'],
        ];

        foreach ($materials as $data) {
            $material = Material::firstOrCreate(
                ['name' => $data['name']],
                [
                    'quantity' => $data['quantity'],
                    'unit' => $data['unit'],
                    'status' => 'Available',
                    'is_hidden' => false,
                ]
            );

            // Ensure status reflects quantity thresholds
            $material->updateStatus();
        }
    }
}



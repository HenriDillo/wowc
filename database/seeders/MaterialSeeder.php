<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['name' => 'Abaca Fiber', 'unit' => 'kg'],
            ['name' => 'Rattan Strips', 'unit' => 'bundle'],
            ['name' => 'Bamboo Sticks', 'unit' => 'pcs'],
            ['name' => 'Dye (Brown)', 'unit' => 'liters'],
            ['name' => 'Glue', 'unit' => 'bottles'],
        ];

        foreach ($materials as $data) {
            $material = Material::firstOrCreate(
                ['name' => $data['name']],
                [
                    'unit' => $data['unit'],
                    'is_hidden' => (bool) random_int(0, 1),
                ]
            );
        }
    }
}



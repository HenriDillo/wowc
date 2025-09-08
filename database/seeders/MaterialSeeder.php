<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['name' => 'Weaved waterlily', 'stock' => 120, 'price' => 45],
            ['name' => 'Rubber', 'stock' => 65, 'price' => 30],
            ['name' => 'Polycanvas', 'stock' => 15, 'price' => 80],
            ['name' => 'Plywood', 'stock' => 200, 'price' => 150],
            ['name' => 'Adhesive', 'stock' => 40, 'price' => 25],
            ['name' => 'Basic color', 'stock' => 70, 'price' => 10],
            ['name' => 'Bleached', 'stock' => 0, 'price' => 12],
            ['name' => 'Natural silac', 'stock' => 95, 'price' => 18],
        ];

        foreach ($materials as $data) {
            Material::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}



<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Handcrafted Soy Candle',
                'description' => 'Aromatherapy soy candle with essential oils.',
                'category' => 'Candles',
                'price' => 249.00,
                'stock' => 30,
                'visible' => true,
            ],
            [
                'name' => 'Woven Rattan Basket',
                'description' => 'Locally crafted rattan basket for storage or decor.',
                'category' => 'Home',
                'price' => 499.00,
                'stock' => 20,
                'visible' => true,
            ],
            [
                'name' => 'Bamboo Plant Holder',
                'description' => 'Minimalist bamboo holder for small plants.',
                'category' => 'Home',
                'price' => 299.00,
                'stock' => 25,
                'visible' => true,
            ],
            [
                'name' => 'Macrame Wall Hanging',
                'description' => 'Boho-style macrame decor handmade with cotton rope.',
                'category' => 'Decor',
                'price' => 699.00,
                'stock' => 12,
                'visible' => true,
            ],
            [
                'name' => 'Coconut Shell Bowl',
                'description' => 'Eco-friendly bowl made from real coconut shells.',
                'category' => 'Kitchen',
                'price' => 199.00,
                'stock' => 40,
                'visible' => true,
            ],
            [
                'name' => 'Capiz Shell Wind Chime',
                'description' => 'Soothing wind chime made with capiz shells.',
                'category' => 'Outdoor',
                'price' => 349.00,
                'stock' => 18,
                'visible' => true,
            ],
            [
                'name' => 'Preorder: Holiday Candle Set',
                'description' => 'Limited seasonal scents. Ships on release date.',
                'category' => 'Candles',
                'price' => 899.00,
                'stock' => 0,
                'visible' => true,
                'release_date' => now()->addWeeks(3)->toDateString(),
            ],
            [
                'name' => 'Abaca Tote Bag',
                'description' => 'Durable handwoven abaca bag for everyday use.',
                'category' => 'Accessories',
                'price' => 799.00,
                'stock' => 15,
                'visible' => true,
            ],
        ];

        foreach ($items as $data) {
            Item::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}



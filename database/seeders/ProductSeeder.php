<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Adidas Samba OG',
            'price' => 120.00,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Nike Dunk Low Panda',
            'price' => 150.00,
            'stock' => 80,
        ]);

        Product::create([
            'name' => 'New Balance 550 White Green',
            'price' => 130.00,
            'stock' => 60,
        ]);
    }
}

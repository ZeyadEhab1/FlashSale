<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // <-- import Str

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'uuid' => Str::uuid(),
            'name' => 'Adidas Samba OG',
            'price' => 120.00,
            'stock' => 50,
        ]);

        Product::create([
            'uuid' => Str::uuid(),
            'name' => 'Nike Dunk Low Panda',
            'price' => 150.00,
            'stock' => 80,
        ]);

        Product::create([
            'uuid' => Str::uuid(),
            'name' => 'New Balance 550 White Green',
            'price' => 130.00,
            'stock' => 60,
        ]);
    }
}

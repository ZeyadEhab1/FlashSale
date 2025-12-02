<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $popularShoes = [
            'Adidas Samba OG',
            'Nike Dunk Low Panda',
            'New Balance 550 White Green',
            'Air Jordan 1 Retro High',
            'Yeezy Boost 350 V2',
            'Converse Chuck Taylor All Star',
            'Vans Old Skool',
            'Puma Suede Classic',
        ];

        return [
            'name' => fake()->randomElement($popularShoes),
            'price' => fake()->randomFloat(2, 80, 300),
            'stock' => fake()->numberBetween(10, 100),
        ];
    }
}

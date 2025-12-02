<?php

namespace Database\Factories;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hold>
 */
class HoldFactory extends Factory
{
    protected $model = Hold::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 3),
            'status' => fake()->randomElement(HoldStatusEnum::cases())->value,
            'expires_at' => fake()->dateTimeBetween('now', '+1 hour'),
        ];
    }
}

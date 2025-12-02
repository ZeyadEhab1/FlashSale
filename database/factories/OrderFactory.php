<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hold_id' => Hold::factory(),
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 3),
            'status' => fake()->randomElement(OrderStatusEnum::cases())->value,
        ];
    }
}

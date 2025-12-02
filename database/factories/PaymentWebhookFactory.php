<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PaymentWebhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentWebhook>
 */
class PaymentWebhookFactory extends Factory
{
    protected $model = PaymentWebhook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idempotency_key' => fake()->uuid(),
            'order_id' => Order::factory(),
            'payload' => [
                'payment_id' => fake()->uuid(),
                'amount' => fake()->randomFloat(2, 50, 500),
                'currency' => 'USD',
                'status' => fake()->randomElement(['success', 'pending', 'failed']),
                'timestamp' => fake()->iso8601(),
            ],
            'processed' => fake()->boolean(),
            'processed_at' => fake()->optional()->dateTime(),
        ];
    }
}

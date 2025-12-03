<?php

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use App\Services\CalculateProductAvailabilityService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name'  => 'Test Product',
        'price' => 100.00,
        'stock' => 50,
    ]);
});

it('can show a product with available quantity', function () {
    $response = $this->getJson("/api/products/{$this->product->uuid}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'uuid',
                'name',
                'price',
                'stock',
                'available',
            ],
        ])
        ->assertJson([
            'data' => [
                'uuid'  => $this->product->uuid,
                'name'  => 'Test Product',
                'price' => '100.00',
                'stock' => 50,
            ],
        ]);
});

it('calculates available quantity correctly when no holds or orders exist', function () {
    $response = $this->getJson("/api/products/{$this->product->uuid}");

    $response->assertStatus(200);
    expect($response->json('data.available'))->toBe(50);
});


it('calculates available quantity correctly with both holds and orders', function () {
    Hold::factory()->create([
        'product_id' => $this->product->id,
        'qty'        => 10,
        'status'     => HoldStatusEnum::ACTIVE,
        'expires_at' => now()->addMinutes(5),
    ]);

    Order::factory()->create([
        'product_id' => $this->product->id,
        'qty'        => 15,
        'status'     => OrderStatusEnum::PAID,
    ]);

    $response = $this->getJson("/api/products/{$this->product->uuid}");

    $response->assertStatus(200);
    expect($response->json('data.available'))->toBe(25);
});



it('returns 404 when product does not exist', function () {
    $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

    $response = $this->getJson("/api/products/{$nonExistentUuid}");

    $response->assertStatus(404);
});

it('excludes non-paid orders from availability calculation', function () {
    Order::factory()->create([
        'product_id' => $this->product->id,
        'qty'        => 10,
        'status'     => OrderStatusEnum::PENDING,
    ]);

    Order::factory()->create([
        'product_id' => $this->product->id,
        'qty'        => 5,
        'status'     => OrderStatusEnum::CANCELLED,
    ]);

    $response = $this->getJson("/api/products/{$this->product->uuid}");

    $response->assertStatus(200);
    expect($response->json('data.available'))->toBe(50);
});


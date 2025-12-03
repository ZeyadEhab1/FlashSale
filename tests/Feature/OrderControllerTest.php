<?php

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use App\Services\CreateOrderService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name'  => 'Test Product',
        'price' => 100.00,
        'stock' => 50,
    ]);

    $this->hold = Hold::factory()->create([
        'product_id' => $this->product->id,
        'qty'        => 5,
        'status'     => HoldStatusEnum::ACTIVE,
        'expires_at' => now()->addMinutes(5),
    ]);
});

it('can create an order from a valid hold', function () {
    $response = $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'uuid',
                'hold_id',
                'product_id',
                'qty',
                'status',
                'hold',
                'product',
            ]])
        ->assertJson([
            'data' => [
                'qty'        => 5,
                'status'     => 'pending',
                'hold_id'    => $this->hold->id,
                'product_id' => $this->product->id,
            ],
        ]);
});

it('marks hold as used when order is created', function () {
    $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $this->hold->refresh();
    expect($this->hold->status)->toBe(HoldStatusEnum::USED);
});


it('returns 422 when hold is not active', function () {
    $this->hold->update(['status' => HoldStatusEnum::USED]);

    $response = $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response->assertJson([
        'message' => 'Hold is not valid',
    ]);
});

it('returns 422 when hold is expired', function () {
    $this->hold->update(['expires_at' => now()->subMinute()]);

    $response = $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response->assertJson([
        'message' => 'Hold is not valid',
    ]);
});

it('creates order with correct product and quantity from hold', function () {
    $response = $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response->assertStatus(201);

    $order = Order::where('uuid', $response->json('data.uuid'))->first();
    expect($order->product_id)->toBe($this->product->id);
    expect($order->qty)->toBe(5);
    expect($order->hold_id)->toBe($this->hold->id);
});

it('creates order with pending status', function () {
    $response = $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response->assertStatus(201);

    $order = Order::where('uuid', $response->json('data.uuid'))->first();
    expect($order->status)->toBe(OrderStatusEnum::PENDING);
});


it('prevents creating multiple orders from the same hold', function () {
    $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response = $this->postJson('/api/orders', [
        'hold_uuid' => $this->hold->uuid,
    ]);

    $response->assertJson([
        'message' => 'Hold is not valid',
    ]);
});


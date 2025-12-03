<?php

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use App\Models\Product;
use App\Services\CreateHoldService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name'  => 'Test Product',
        'price' => 100.00,
        'stock' => 50,
    ]);
    $this->holdsUrl = '/api/holds';
    $this->productsUrl = '/api/products';
});

it('can create a hold for a product', function () {
    $response = $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 5,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'uuid',
                'product_id',
                'qty',
                'status',
                'expires_at',
                'product' => [
                    'uuid',
                    'name',
                    'price',
                    'stock',
                    'available',
                ],
            ],
        ]);
    $response->assertJson([
        'data' => [
            'qty'    => 5,
            'status' => 'active',
        ],
    ]);

    expect($response->json('data.product'))->not->toBeNull();
    expect($response->json('data.product.uuid'))->toBe($this->product->uuid);
});

it('creates a hold with correct expiration time', function () {
    $response = $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 3,
    ]);
    $response->assertStatus(201);

    $hold = Hold::where('uuid', $response->json('data.uuid'))->first();
    expect($hold->expires_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($hold->expires_at->isFuture())->toBeTrue();
});


it('validates qty is at least 1', function () {
    $response = $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 0,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['qty']);
});

it('returns 422 when there is not enough stock', function () {
    $response = $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 100,
    ]);

    $response->assertJson([
            'message' => 'Not enough stock',
        ]);
});

it('reduces available stock when hold is created', function () {
    $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 10,
    ]);

    $productResponse = $this->getJson("$this->productsUrl/{$this->product->uuid}");
    expect($productResponse->json('data.available'))->toBe(40);
});


it('can create multiple holds for the same product', function () {
    $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 10,
    ]);

    $response = $this->postJson($this->holdsUrl, [
        'product_uuid' => $this->product->uuid,
        'qty'          => 15,
    ]);

    $response->assertStatus(201);

    $productResponse = $this->getJson("$this->productsUrl/{$this->product->uuid}");
    expect($productResponse->json('data.available'))->toBe(25);
});

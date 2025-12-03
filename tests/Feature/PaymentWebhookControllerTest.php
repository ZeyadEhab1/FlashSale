<?php

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Hold;
use App\Models\Order;
use App\Models\PaymentWebhook;
use App\Models\Product;
use App\Services\ProcessPaymentWebhookService;

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

    $this->order = Order::factory()->create([
        'hold_id'    => $this->hold->id,
        'product_id' => $this->product->id,
        'qty'        => 5,
        'status'     => OrderStatusEnum::PENDING,
    ]);
});

it('can process a payment webhook with success status', function () {
    $response = $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'test-ref-123',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'success',
        'payload'               => ['payment_id' => 'pay_123'],
    ]);

    $response->assertStatus(200)
        ->assertJson(['ok' => true]);

    $this->order->refresh();
    expect($this->order->status)->toBe(OrderStatusEnum::PAID);
});

it('can process a payment webhook with failure status', function () {
    $response = $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'test-ref-456',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'failure',
        'payload'               => ['error' => 'Payment failed'],
    ]);

    $response->assertStatus(200)
        ->assertJson(['ok' => true]);

    $this->order->refresh();
    expect($this->order->status)->toBe(OrderStatusEnum::CANCELLED);
});

it('cancels hold when payment fails', function () {
    $this->hold->update(['status' => HoldStatusEnum::USED]);

    $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'test-ref-789',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'failure',
    ]);

    $this->hold->refresh();
    expect($this->hold->status)->toBe(HoldStatusEnum::CANCELLED);
});


it('creates webhook record when processing', function () {
    $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'unique-ref-123',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'success',
    ]);

    $webhook = PaymentWebhook::where('transaction_reference', 'unique-ref-123')->first();
    expect($webhook)->not->toBeNull();
});

it('handles duplicate transaction_reference idempotently', function () {
    $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'duplicate-ref',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'success',
    ]);

    $response = $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'duplicate-ref',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'success',
    ]);

    $response->assertStatus(200);

    $webhooks = PaymentWebhook::where('transaction_reference', 'duplicate-ref')->get();
    expect($webhooks->count())->toBe(1);
});

it('does not process webhook again if already processed', function () {
    $webhook = PaymentWebhook::factory()->create([
        'transaction_reference' => 'already-processed',
        'order_id'              => $this->order->id,
        'processed'             => true,
        'processed_at'          => now(),
    ]);

    $this->order->update(['status' => OrderStatusEnum::PAID]);

    $response = $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'already-processed',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'failure',
    ]);

    $response->assertStatus(200);

    $this->order->refresh();
    expect($this->order->status)->toBe(OrderStatusEnum::PAID); // Should remain paid
});

it('marks webhook as processed after handling', function () {
    $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'process-test',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'success',
    ]);

    $webhook = PaymentWebhook::where('transaction_reference', 'process-test')->first();
    expect($webhook->processed)->toBeTrue();
    expect($webhook->processed_at)->not->toBeNull();
});


it('does not change order status if order is already paid', function () {
    $this->order->update(['status' => OrderStatusEnum::PAID]);

    $this->postJson('/api/payments/webhook', [
        'transaction_reference' => 'paid-order-test',
        'order_uuid'            => $this->order->uuid,
        'status'                => 'failure',
    ]);

    $this->order->refresh();
    expect($this->order->status)->toBe(OrderStatusEnum::PAID);
});


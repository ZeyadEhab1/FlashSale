<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentWebhook;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcessPaymentWebhookService
{
    protected $orderService;

    public function __construct(CreateOrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    public function handle(array $data)
    {
        try {
            $webhook = PaymentWebhook::create([
                'idempotency_key' => $data['idempotency_key'],
                'order_id'        => $data['order_id'] ?? null,
                'payload'         => $data['payload'] ?? null,
                'processed'       => false,
            ]);
        } catch (QueryException $e) {
            $existingWebhook = PaymentWebhook::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existingWebhook && $existingWebhook->processed) {
                return $existingWebhook;
            }
            $webhook = $existingWebhook;
        }

        if ($webhook->order_id) {
            $order = Order::where('id', $webhook->order_id)->first();
            if ($order) {
                $this->processWebhookAgainstOrder($webhook, $order, $data['status'] ?? null);
            } else {
                // order not found: leave unprocessed, will be retried by job
            }
        } else {
            // webhook without order id — leave for investigation (or map via external refs)
        }

        return $webhook;
    }

    private function processWebhookAgainstOrder($webhook, $order, ?string $status)
    {
        if ($webhook->processed) {
            return;
        }
        DB::transaction(function () use ($webhook, $order, $status) {
            $order = Order::where('id', $order->id)->lockForUpdate()->first();
            if ($order->status === 'paid') {
                $webhook->processed = true;
                $webhook->processed_at = now();
                $webhook->save();
                return;
            }
            if ($status === 'success') {
                $order->status = 'paid';
                $order->save();
            } else {
                $order->status = 'cancelled';
                $order->save();

                $hold = $order->hold()->lockForUpdate()->first();
                if ($hold && $hold->status === 'used') {
                    $hold->status = 'cancelled';
                    $hold->save();
                }
            }
            $webhook->processed = true;
            $webhook->processed_at = now();
            $webhook->save();
        });
    }


}


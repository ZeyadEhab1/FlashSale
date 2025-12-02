<?php

namespace App\Http\Controllers;


use App\Http\Requests\PaymentWebhookRequest;
use App\Services\ProcessPaymentWebhookService;

class PaymentWebhookController extends Controller
{
    public function handle(PaymentWebhookRequest $request, ProcessPaymentWebhookService $service)
    {
        $data = $request->validated();

        $webhook = $service->handle([
            'idempotency_key' => $data['idempotency_key'],
            'order_id'        => $data['order_id'] ?? null,
            'status'          => $data['status'],
            'payload'         => $data['payload'] ?? $request->all(),
        ]);

        return response()->json(['ok' => true]);
    }
}

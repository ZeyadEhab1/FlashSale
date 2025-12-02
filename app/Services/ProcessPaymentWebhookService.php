<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\PaymentWebhook;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhookService
{
    private const PAYMENT_STATUS_SUCCESS = 'success';
    private const PAYMENT_STATUS_FAILURE = 'failure';

    public function handle(array $data): PaymentWebhook
    {
        $webhook = $this->findOrCreateWebhook($data);

        if ($webhook->isAlreadyProcessed()) {
            return $webhook;
        }

        $order = $this->resolveOrder($data, $webhook);

        if ($order === null) {
            $this->logUnprocessableWebhook($webhook, 'Order not found');
            return $webhook;
        }

        $this->processWebhookForOrder($webhook, $order, $data['status']);

        return $webhook->fresh();
    }


    private function findOrCreateWebhook(array $data): PaymentWebhook
    {

        try {
            return PaymentWebhook::create([
                'transaction_reference' => $data['transaction_reference'],
                'order_uuid'            => $data['order_uuid'],
                'payload'               => $data['payload'] ?? null,
                'processed'             => false,
            ]);
        } catch (QueryException $e) {
            $existingWebhook = PaymentWebhook::where('transaction_reference', $data['transaction_reference'])
                ->firstOrFail();

            if ($existingWebhook->isAlreadyProcessed()) {
                Log::info('Webhook already processed', [
                    'transaction_reference' => $data['transaction_reference'],
                ]);
            }

            return $existingWebhook;
        }
    }

    private function resolveOrder(array $data, PaymentWebhook $webhook): ?Order
    {
        if (!empty($data['order_uuid'])) {
            $order = Order::where('uuid', $data['order_uuid'])->first();
            if ($order !== null) {
                return $order;
            }
        }

        if ($webhook->order_id !== null) {
            return Order::find($webhook->order_id);
        }

        return null;
    }


    private function processWebhookForOrder(PaymentWebhook $webhook, Order $order, string $paymentStatus): void
    {
        DB::transaction(function () use ($webhook, $order, $paymentStatus) {
            $lockedOrder = Order::where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isOrderAlreadyPaid($lockedOrder)) {
                $this->markWebhookAsProcessed($webhook);
                return;
            }

            $this->updateOrderStatus($lockedOrder, $paymentStatus);
            $this->handleOrderCancellation($lockedOrder, $paymentStatus);
            $this->markWebhookAsProcessed($webhook);
        }, 5);
    }


    private function isOrderAlreadyPaid(Order $order): bool
    {
        return $order->status === OrderStatusEnum::PAID;
    }


    private function updateOrderStatus(Order $order, string $paymentStatus): void
    {
        $orderStatus = $this->mapPaymentStatusToOrderStatus($paymentStatus);
        $order->status = $orderStatus;
        $order->save();
    }


    private function mapPaymentStatusToOrderStatus(string $paymentStatus): OrderStatusEnum
    {
        return match ($paymentStatus) {
            self::PAYMENT_STATUS_SUCCESS => OrderStatusEnum::PAID,
            self::PAYMENT_STATUS_FAILURE => OrderStatusEnum::CANCELLED,
            default                      => throw new \InvalidArgumentException("Invalid payment status: {$paymentStatus}"),
        };
    }


    private function handleOrderCancellation(Order $order, string $paymentStatus): void
    {
        if ($paymentStatus !== self::PAYMENT_STATUS_FAILURE) {
            return;
        }

        $hold = $order->hold()
            ->lockForUpdate()
            ->first();

        if ($hold === null) {
            return;
        }

        if ($hold->status === HoldStatusEnum::USED) {
            $hold->status = HoldStatusEnum::CANCELLED;
            $hold->save();
        }
    }


    private function markWebhookAsProcessed(PaymentWebhook $webhook): void
    {
        $webhook->processed = true;
        $webhook->processed_at = now();
        $webhook->save();
    }


    private function logUnprocessableWebhook(PaymentWebhook $webhook, string $reason): void
    {
        Log::warning('Webhook cannot be processed', [
            'webhook_id'            => $webhook->id,
            'transaction_reference' => $webhook->transaction_reference,
            'reason'                => $reason,
        ]);
    }
}

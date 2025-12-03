<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderService
{
    public function createOrderFromHold(string $holdUuid): Order
    {
        return DB::transaction(function () use ($holdUuid) {
            $hold = Hold::where('uuid', $holdUuid)->lockForUpdate()->firstOrFail();
            if ($hold->status->value !== 'active' || $hold->expires_at->isPast()) {
                throw new Exception("Hold is not valid");
            }


             //STEP Call the checkout process : $this->checkout($hold);

            $hold->status = 'used';
            $hold->save();

            $order = Order::create([
                'hold_id'    => $hold->id,
                'product_id' => $hold->product_id,
                'qty'        => $hold->qty,
                'status'     => 'pending',
            ]);

            return $order;
        }, 5);
    }


    // Fake checkout method (Payment placeholder)
    protected function checkout(Hold $hold): void
    {
        // Simulated payment logic:
        // This is where you would integrate the payment gateway.
        // Example: $paymentResponse = $this->paymentGateway->charge($hold->qty * $hold->product->price);
        // if (!$paymentResponse->isSuccessful()) {
        //     throw new Exception("Payment failed", 422);
        // }
    }
}


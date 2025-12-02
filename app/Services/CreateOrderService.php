<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderService
{
    // get the hold
    // check if the hold is active
    // if so create the order with status pending

    public function createOrderFromHold(int $holdId): Order
    {
        return DB::transaction(function () use ($holdId) {
            $hold = Hold::where('id', $holdId)->lockForUpdate()->firstOrFail();
            if ($hold->status->value !== 'active' || $hold->expires_at->isPast()) {
                throw new Exception("Hold is not valid", 422);
            }

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
}


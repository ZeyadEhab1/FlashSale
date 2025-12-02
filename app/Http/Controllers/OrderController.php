<?php

namespace App\Http\Controllers;


use App\Http\Requests\createOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\CreateOrderService;
use Exception;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, CreateOrderService $orderService): OrderResource
    {
        $data = $request->validated();

            $order = $orderService->createOrderFromHold($data['hold_uuid']);
            $order->load(['hold', 'product']);

            return new OrderResource($order);
    }
}

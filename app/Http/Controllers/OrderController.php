<?php

namespace App\Http\Controllers;


use App\Http\Requests\createOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\CreateOrderService;
use Exception;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, CreateOrderService $orderService)
    {
        $data = $request->validated();

        try {
            $order = $orderService->createOrderFromHold($data['hold_id']);
            $order->load(['hold', 'product']);

            return new OrderResource($order);


        }catch (Exception $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getCode() === 422 ? 422 : 400
            );
        }
    }
}

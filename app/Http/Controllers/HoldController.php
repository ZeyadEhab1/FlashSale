<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHoldRequest;
use App\Http\Resources\HoldResource;
use App\Services\CreateHoldService;
use Exception;

class HoldController extends Controller
{
    public function store(StoreHoldRequest $request, CreateHoldService $holdService)
    {
        $data = $request->validated();


        $hold = $holdService->createHold($data['product_uuid'], $data['qty']);
        $hold->load('product');
        return new HoldResource($hold);

    }
}

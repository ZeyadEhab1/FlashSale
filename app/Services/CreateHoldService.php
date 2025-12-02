<?php

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateHoldService
{
    protected CalculateProductAvailabilityService $availabilityService;
    protected int $holdTtlSeconds = 120;

    /**
     * @return mixed
     */
    public function __construct(CalculateProductAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function createHold(string $productUuid, int $qty): Hold
    {
        $hold = DB::transaction(function () use ($productUuid, $qty) {
            $product = Product::where('uuid', $productUuid)->lockForUpdate()->firstOrFail();
            $available = $this->availabilityService->getAvailableProducts($product);

            if ($available < $qty) {
                throw new Exception("Not enough stock", 422);
            }


            $hold = Hold::create([
                'product_id' => $product->id,
                'qty'        => $qty,
                'status'     => HoldStatusEnum::ACTIVE,
                'expires_at' => now()->addSeconds($this->holdTtlSeconds),
            ]);
            return $hold;
        }, 5);

        return $hold;

    }
}


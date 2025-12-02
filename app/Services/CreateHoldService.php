<?php

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateHoldService
{
    protected $availabilityService;
    protected $holdTtlSeconds = 120; // 2 minutes

    /**
     * @return mixed
     */
    public function __construct(CalculateProductAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function createHold(int $productId, int $qty): Hold
    {
        // find the product
        // see if it is available
        // if checks
        // create the hold
        $hold = DB::transaction(function () use ($productId, $qty) {
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
            $available = $this->availabilityService->getAvailable($product);

            if ($qty <= 0) {
                throw new Exception("Quantity must be > 0");
            }
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


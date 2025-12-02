<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CalculateProductAvailabilityService
{
    public function getAvailableProducts(Product $product)
    {
        $activeHolds = DB::table('holds')
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->sum('qty');

        $paidOrders = DB::table('orders')
            ->where('product_id', $product->id)
            ->where('status', 'paid')
            ->sum('qty');

        return max(0, $product->stock - $activeHolds - $paidOrders);
    }
}


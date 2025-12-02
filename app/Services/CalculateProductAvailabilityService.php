<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CalculateProductAvailabilityService
{
    public function getAvailable(Product $product)
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

    public function lockProductRow(int $productId): Product
    {
        return DB::transaction(function () use ($productId){
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
            return $product;
            }, 5);
    }
}


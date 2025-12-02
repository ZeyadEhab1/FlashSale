<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CalculateProductAvailabilityService;

class ProductController extends Controller
{
    public function show(Product $product, CalculateProductAvailabilityService $availabilityService)
    {
        $available = $availabilityService->getAvailableProducts($product);

        return new ProductResource(
            $product->setAttribute('available', $available)
        );
    }
}

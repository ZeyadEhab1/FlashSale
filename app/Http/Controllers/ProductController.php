<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CalculateProductAvailabilityService;

class ProductController extends Controller
{
    public function show($id, CalculateProductAvailabilityService $availabilityService)
    {
        $product = Product::findOrFail($id);
        $available = $availabilityService->getAvailable($product);

        return new ProductResource(
            $product->setAttribute('available', $available)
        );
    }
}

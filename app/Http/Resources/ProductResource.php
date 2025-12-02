<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid'      => $this->uuid,
            'name'      => $this->name,
            'price'     => $this->price,
            'stock'     => $this->stock,
            'available' => $this->available,
        ];
    }
}

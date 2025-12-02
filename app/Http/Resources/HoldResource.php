<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;

class HoldResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid'       => $this->uuid,
            'product_id' => $this->product_id,
            'qty'        => $this->qty,
            'status'     => $this->status->value ?? $this->status,
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'product'    => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'hold_id'    => $this->hold_id,
            'product_id' => $this->product_id,
            'qty'        => $this->qty,
            'status'     => $this->status->value,
            'hold'       => new HoldResource($this->whenLoaded('hold')),
            'product'    => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

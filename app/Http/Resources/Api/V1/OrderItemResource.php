<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product_id' => $this->product_id,

            'product_name' => $this->product_name,

            'product_sku' => $this->product_sku,

            'unit_price' => $this->unit_price,

            'quantity' => $this->quantity,

            'subtotal' => $this->subtotal,

            'selling_unit' => $this->selling_unit,

            'base_unit' => $this->base_unit,

            'base_quantity' => $this->base_quantity,
        ];
    }
}
<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'slug' => $this->product?->slug,
                'sku' => $this->product?->sku,
                'brand' => $this->product?->brand,
                'generic_name' => $this->product?->generic_name,

                'price' => $this->product?->price,
                'sale_price' => $this->product?->sale_price,

                'image' => $this->product?->image_url,

                'base_unit' => $this->product?->base_unit,
                'selling_unit' => $this->product?->selling_unit,
                'units_per_selling_unit' =>
                    $this->product?->units_per_selling_unit,

                'requires_prescription' =>
                    (bool) $this->product?->requires_prescription,

                'is_active' =>
                    (bool) $this->product?->is_active,
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
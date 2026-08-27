<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $inventory = $this->inventory;

        $availableQuantity = $inventory?->available_quantity ?? 0;

        return [
            /*
            |--------------------------------------------------------------------------
            | Basic Product Information
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'sku' => $this->sku,

            'barcode' => $this->barcode,

            'brand' => $this->brand,

            'generic_name' => $this->generic_name,

            'description' => $this->description,

            'dosage_form' => $this->dosage_form,

            'strength' => $this->strength,

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'price' => $this->price,

            /*
            |--------------------------------------------------------------------------
            | Prescription / Product Status
            |--------------------------------------------------------------------------
            */

            'requires_prescription' => $this->requires_prescription,

            'is_active' => $this->is_active,

            'is_featured' => $this->is_featured,

            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            'image' => $this->image_url,

            /*
            |--------------------------------------------------------------------------
            | Units
            |--------------------------------------------------------------------------
            */

            'units' => [
                'base_unit' => $this->resource->baseUnitLabel(),

                'selling_unit' => $this->resource->sellingUnitLabel(),

                'units_per_selling_unit' =>
                    $this->units_per_selling_unit,

                'allow_partial_sale' =>
                    $this->allow_partial_sale,

                'packaging_description' =>
                    $this->packaging_description,
            ],

            /*
            |--------------------------------------------------------------------------
            | Stock / Availability
            |--------------------------------------------------------------------------
            |
            | Customer-facing inventory information only.
            |
            */

            'stock' => [
                'quantity' => $inventory?->quantity ?? 0,

                'reserved_quantity' =>
                    $inventory?->reserved_quantity ?? 0,

                'available_quantity' =>
                    $availableQuantity,

                'minimum_stock' =>
                    $inventory?->minimum_stock ?? 0,

                'is_low_stock' =>
                    $inventory?->is_low_stock ?? false,

                'in_stock' =>
                    $availableQuantity > 0,
            ],

            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            'category' => $this->whenLoaded(
                'category',
                function () {
                    return [
                        'id' => $this->category->id,

                        'name' => $this->category->name,

                        'slug' => $this->category->slug,
                    ];
                }
            ),
        ];
    }
}
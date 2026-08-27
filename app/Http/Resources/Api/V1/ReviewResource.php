<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],

            'product' => [
                'id' => $this->reviewable?->id,
                'name' => $this->reviewable?->name,
                'slug' => $this->reviewable?->slug,
            ],

            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_approved' => $this->is_approved,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
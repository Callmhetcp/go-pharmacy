<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'reference_number' => $this->reference_number,

            'doctor_name' => $this->doctor_name,

            'hospital_name' => $this->hospital_name,

            'prescription_date' => $this->prescription_date?->toDateString(),

            'file_type' => $this->file_type,

            'notes' => $this->notes,

            'status' => $this->status,

            'rejection_reason' => $this->rejection_reason,

            'review_notes' => $this->review_notes,

            'reviewed_at' => $this->reviewed_at?->toISOString(),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
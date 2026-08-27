<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'payment_reference' => $this->payment_reference,

            'transaction_reference' => $this->transaction_reference,

            'gateway' => $this->gateway,

            'gateway_transaction_id' => $this->gateway_transaction_id,

            'amount' => $this->amount,

            'currency' => $this->currency,

            'status' => $this->status,

            'payment_method' => $this->payment_method,

            'gateway_message' => $this->gateway_message,

            'paid_at' => $this->paid_at?->toISOString(),

            'verified_at' => $this->verified_at?->toISOString(),

            'refunded_amount' => $this->refunded_amount,

            'refunded_at' => $this->refunded_at?->toISOString(),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
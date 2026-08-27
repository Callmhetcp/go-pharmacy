<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_reference',
        'transaction_reference',

        'gateway',
        'gateway_transaction_id',

        'amount',
        'currency',

        'status',
        'payment_method',

        'gateway_message',
        'gateway_response',

        'paid_at',
        'verified_at',

        'refunded_amount',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',

        'gateway_response' => 'array',

        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function isPending(): bool
    {
        return in_array(
            $this->status,
            ['pending', 'processing'],
            true
        );
    }

    public function isFailed(): bool
    {
        return in_array(
            $this->status,
            ['failed', 'cancelled'],
            true
        );
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /*
    |--------------------------------------------------------------------------
    | Gateway Helpers
    |--------------------------------------------------------------------------
    */

    public function isFlutterwave(): bool
    {
        return $this->gateway === 'flutterwave';
    }

    public function isPaystack(): bool
    {
        return $this->gateway === 'paystack';
    }

    public function isOpay(): bool
    {
        return $this->gateway === 'opay';
    }
}
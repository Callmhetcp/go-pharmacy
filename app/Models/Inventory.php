<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'reserved_quantity',
        'minimum_stock',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'minimum_stock' => 'integer',
    ];

    protected $appends = [
        'available_quantity',
        'is_low_stock',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getAvailableQuantityAttribute(): int
    {
        return max(
            0,
            (int) $this->quantity - (int) $this->reserved_quantity
        );
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_quantity <= (int) $this->minimum_stock;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'brand',
        'generic_name',
        'description',
        'price',
        'cost_price',
        'dosage_form',
        'strength',
        'requires_prescription',
        'is_active',
        'is_featured',
        'image',
        'minimum_stock',

        'base_unit',
        'selling_unit',
        'units_per_selling_unit',
        'allow_partial_sale',
        'packaging_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'allow_partial_sale' => 'boolean',
        'units_per_selling_unit' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

     public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function baseUnitsForSellingQuantity(int $quantity): int
    {
        return $quantity * max(
            1,
            (int) $this->units_per_selling_unit
        );
    }

    public function sellingQuantityFromBaseUnits(int $baseUnits): int
    {
        if ($baseUnits <= 0) {
            return 0;
        }

        return (int) ceil(
            $baseUnits / max(
                1,
                (int) $this->units_per_selling_unit
            )
        );
    }

    public function sellingUnitLabel(): string
    {
        return $this->selling_unit ?: 'piece';
    }

    public function baseUnitLabel(): string
    {
        return $this->base_unit ?: 'piece';
    }

    /**
     * Get wishlist entries for this product.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
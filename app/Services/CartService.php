<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class CartService
{
    protected string $sessionKey = 'go_pharmacy_cart';

    /**
     * Get the current cart.
     */
    public function getCart(): array
    {
        $cart = Session::get($this->sessionKey, []);

        return $this->buildCartResponse($cart);
    }

    /**
     * Add a product to the cart.
     */
    public function add(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Quantity must be at least 1.'
            );
        }

        $product = $this->getActiveProduct($productId);

        $this->validateStock($product, $quantity);

        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $newQuantity =
                $cart[$productId]['quantity'] + $quantity;

            $this->validateStock($product, $newQuantity);

            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ];
        }

        Session::put($this->sessionKey, $cart);

        return $this->buildCartResponse($cart);
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Quantity must be at least 1.'
            );
        }

        $product = $this->getActiveProduct($productId);

        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            throw new RuntimeException(
                'Product is not in your cart.'
            );
        }

        $this->validateStock($product, $quantity);

        $cart[$productId]['quantity'] = $quantity;

        Session::put($this->sessionKey, $cart);

        return $this->buildCartResponse($cart);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(int $productId): array
    {
        $cart = $this->getCart();

        unset($cart[$productId]);

        Session::put($this->sessionKey, $cart);

        return $this->buildCartResponse($cart);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    /**
     * Build a frontend-ready cart response.
     */
    protected function buildCartResponse(array $cart): array
    {
        if (empty($cart)) {
            return [
                'items' => [],
                'subtotal' => 0,
                'item_count' => 0,
            ];
        }

        $productIds = array_keys($cart);

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->with([
                'category',
                'inventory',
            ])
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;
        $itemCount = 0;

        foreach ($cart as $productId => $cartItem) {
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            $quantity = (int) $cartItem['quantity'];

            $unitPrice = (float) $product->price;

            $itemSubtotal = $unitPrice * $quantity;

            $subtotal += $itemSubtotal;
            $itemCount += $quantity;

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->image_url,

                'quantity' => $quantity,

                'unit_price' => number_format(
                    $unitPrice,
                    2,
                    '.',
                    ''
                ),

                'subtotal' => number_format(
                    $itemSubtotal,
                    2,
                    '.',
                    ''
                ),

                'selling_unit' =>
                    $product->sellingUnitLabel(),

                'base_unit' =>
                    $product->baseUnitLabel(),

                'units_per_selling_unit' =>
                    $product->units_per_selling_unit,

                'requires_prescription' =>
                    $product->requires_prescription,

                'stock' => [
                    'available_quantity' =>
                        $product->inventory?->available_quantity ?? 0,

                    'is_low_stock' =>
                        $product->inventory?->is_low_stock ?? false,

                    'in_stock' =>
                        ($product->inventory?->available_quantity ?? 0) > 0,
                ],

                'category' => $product->category
                    ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ]
                    : null,
            ];
        }

        return [
            'items' => $items,
            'subtotal' => number_format(
                $subtotal,
                2,
                '.',
                ''
            ),
            'item_count' => $itemCount,
        ];
    }

    /**
     * Load an active product.
     */
    protected function getActiveProduct(int $productId): Product
    {
        $product = Product::query()
            ->whereKey($productId)
            ->where('is_active', true)
            ->with('inventory')
            ->first();

        if (!$product) {
            throw new RuntimeException(
                'This product is no longer available.'
            );
        }

        return $product;
    }

    /**
     * Validate currently available inventory.
     *
     * This checks stock but DOES NOT reserve it.
     */
    protected function validateStock(
        Product $product,
        int $sellingQuantity
    ): void {
        $inventory = $product->inventory;

        if (!$inventory) {
            throw new RuntimeException(
                "Inventory record not found for {$product->name}."
            );
        }

        $baseQuantity =
            $product->baseUnitsForSellingQuantity(
                $sellingQuantity
            );

        $availableBaseQuantity =
            $inventory->available_quantity;

        if ($baseQuantity > $availableBaseQuantity) {
            $availableSellingQuantity =
                $product->sellingQuantityFromBaseUnits(
                    $availableBaseQuantity
                );

            throw new RuntimeException(
                "Insufficient stock for {$product->name}. "
                . "Only {$availableSellingQuantity} "
                . "{$product->sellingUnitLabel()}(s) available."
            );
        }
    }
}
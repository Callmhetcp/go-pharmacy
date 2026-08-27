<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index(Request $request): Response
    {
        $cart = $request->session()->get('cart', []);

        $cartCount = collect($cart)->sum(
            fn ($item) => (int) ($item['quantity'] ?? 0)
        );

        $cartSubtotal = collect($cart)->sum(
            fn ($item) => (float) ($item['subtotal'] ?? 0)
        );

        return Inertia::render('Cart/Index', [
            'cart' => array_values($cart),
            'cartCount' => $cartCount,
            'cartSubtotal' => $cartSubtotal,
        ]);
    }

    /**
     * Add a product to the cart.
     *
     * Cart quantity represents SELLING UNITS.
     *
     * Example:
     * - Base unit: tablet
     * - Selling unit: pack
     * - Units per selling unit: 10
     * - Customer quantity: 2
     *
     * This means the customer is buying 2 packs = 20 tablets.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $product = Product::query()
            ->with('inventory')
            ->where('id', $validated['product_id'])
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return back()->with(
                'error',
                'Product not found or is no longer available.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product packaging
        |--------------------------------------------------------------------------
        */

        $unitsPerSellingUnit = max(
            1,
            (int) $product->units_per_selling_unit
        );

        $baseUnit = $product->base_unit ?: 'piece';
        $sellingUnit = $product->selling_unit ?: 'piece';

        /*
        |--------------------------------------------------------------------------
        | Available inventory
        |--------------------------------------------------------------------------
        |
        | Inventory quantity is stored in BASE UNITS.
        |
        | Example:
        |
        | 100 tablets in stock
        | 10 tablets per pack
        | = 10 packs available
        |
        */

        $availableBaseUnits = 0;

        if ($product->inventory) {
            $availableBaseUnits = max(
                0,
                (int) $product->inventory->quantity
                - (int) $product->inventory->reserved_quantity
            );
        }

        if ($availableBaseUnits <= 0) {
            return back()->with(
                'error',
                'This product is currently out of stock.'
            );
        }

        $availableSellingUnits = intdiv(
            $availableBaseUnits,
            $unitsPerSellingUnit
        );

        /*
        |--------------------------------------------------------------------------
        | Products sold as individual base units
        |--------------------------------------------------------------------------
        */

        if ($unitsPerSellingUnit === 1) {
            $availableSellingUnits = $availableBaseUnits;
        }

        if ($availableSellingUnits <= 0) {
            return back()->with(
                'error',
                "This product does not have enough stock for one {$sellingUnit}."
            );
        }

        $quantity = (int) $validated['quantity'];

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        $cart = $request->session()->get('cart', []);

        $productId = (string) $product->id;

        /*
        |--------------------------------------------------------------------------
        | Existing cart item
        |--------------------------------------------------------------------------
        */

        if (isset($cart[$productId])) {
            $newQuantity =
                (int) $cart[$productId]['quantity']
                + $quantity;

            if ($newQuantity > $availableSellingUnits) {
                return back()->with(
                    'error',
                    "Only {$availableSellingUnits} {$sellingUnit}(s) are currently available."
                );
            }

            $cart[$productId]['quantity'] = $newQuantity;

            $cart[$productId]['base_quantity'] =
            $newQuantity * $unitsPerSellingUnit;

            $cart[$productId]['subtotal'] =
                (float) $cart[$productId]['unit_price']
                * $newQuantity;

            /*
            |--------------------------------------------------------------------------
            | Refresh packaging information
            |--------------------------------------------------------------------------
            |
            | This protects the cart from stale packaging information if the
            | product was edited after it was first added.
            |
            */

            $cart[$productId]['base_unit'] = $baseUnit;
            $cart[$productId]['selling_unit'] = $sellingUnit;
            $cart[$productId]['units_per_selling_unit'] =
                $unitsPerSellingUnit;
            $cart[$productId]['packaging_description'] =
                $product->packaging_description;
        }

        /*
        |--------------------------------------------------------------------------
        | New cart item
        |--------------------------------------------------------------------------
        */

        else {
            if ($quantity > $availableSellingUnits) {
                return back()->with(
                    'error',
                    "Only {$availableSellingUnits} {$sellingUnit}(s) are currently available."
                );
            }

            $unitPrice = (float) $product->price;

            $cart[$productId] = [
                'product_id' => $product->id,

                'product_name' => $product->name,

                'product_sku' => $product->sku,

                'unit_price' => $unitPrice,

                /*
                |--------------------------------------------------------------------------
                | Customer-facing quantity
                |--------------------------------------------------------------------------
                |
                | This is selling units.
                |
                | Example:
                | quantity = 2
                | selling_unit = pack
                |
                | Customer is buying 2 packs.
                |
                */

                'quantity' => $quantity,

                'subtotal' => $unitPrice * $quantity,

                'image' => $product->image,

                /*
                |--------------------------------------------------------------------------
                | Packaging
                |--------------------------------------------------------------------------
                */

                'base_unit' => $baseUnit,

                'selling_unit' => $sellingUnit,

                'units_per_selling_unit' => $unitsPerSellingUnit,

                'packaging_description' =>
                    $product->packaging_description,

                /*
                |--------------------------------------------------------------------------
                | Base quantity
                |--------------------------------------------------------------------------
                |
                | This is useful for checkout, order processing and inventory.
                |
                */

                'base_quantity' =>
                    $quantity * $unitsPerSellingUnit,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Save cart
        |--------------------------------------------------------------------------
        */

        $request->session()->put('cart', $cart);

        return back()->with(
            'success',
            "{$product->name} has been added to your cart."
        );
    }

    /**
     * Update cart quantity.
     *
     * Quantity represents SELLING UNITS.
     */
    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $cart = $request->session()->get('cart', []);

        $productId = (string) $product->id;

        if (! isset($cart[$productId])) {
            return back()->with(
                'error',
                'Product is not in your cart.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product packaging
        |--------------------------------------------------------------------------
        */

        $unitsPerSellingUnit = max(
            1,
            (int) $product->units_per_selling_unit
        );

        $sellingUnit = $product->selling_unit ?: 'piece';

        /*
        |--------------------------------------------------------------------------
        | Check available stock
        |--------------------------------------------------------------------------
        */

        $availableBaseUnits = 0;

        if ($product->inventory) {
            $availableBaseUnits = max(
                0,
                (int) $product->inventory->quantity
                - (int) $product->inventory->reserved_quantity
            );
        }

        $availableSellingUnits = intdiv(
            $availableBaseUnits,
            $unitsPerSellingUnit
        );

        if ($unitsPerSellingUnit === 1) {
            $availableSellingUnits = $availableBaseUnits;
        }

        $quantity = (int) $validated['quantity'];

        if ($quantity > $availableSellingUnits) {
            return back()->with(
                'error',
                "Only {$availableSellingUnits} {$sellingUnit}(s) are currently available."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Update cart
        |--------------------------------------------------------------------------
        */

        $price = (float) (
            $cart[$productId]['unit_price']
            ?? $product->price
            ?? 0
        );

        $cart[$productId]['quantity'] = $quantity;

        $cart[$productId]['subtotal'] =
            $price * $quantity;

        $cart[$productId]['base_unit'] =
            $product->base_unit ?: 'piece';

        $cart[$productId]['selling_unit'] =
            $sellingUnit;

        $cart[$productId]['units_per_selling_unit'] =
            $unitsPerSellingUnit;

        $cart[$productId]['base_quantity'] =
            $quantity * $unitsPerSellingUnit;

        $cart[$productId]['packaging_description'] =
            $product->packaging_description;

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $request->session()->put('cart', $cart);

        return back()->with(
            'success',
            'Cart updated successfully.'
        );
    }

    /**
     * Remove product from cart.
     */
    public function destroy(
        Request $request,
        Product $product
    ): RedirectResponse {
        $cart = $request->session()->get('cart', []);

        $productId = (string) $product->id;

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        $request->session()->put('cart', $cart);

        return back()->with(
            'success',
            'Product removed from your cart.'
        );
    }

    /**
     * Clear entire cart.
     */
    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with(
            'success',
            'Your cart has been cleared.'
        );
    }
}

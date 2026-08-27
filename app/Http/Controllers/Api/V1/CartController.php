<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {
    }

    /**
     * Display the current cart.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->cartService->getCart(),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request): JsonResponse
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

        try {
            $cart = $this->cartService->add(
                (int) $validated['product_id'],
                (int) $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart.',
                'data' => $cart,
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(
        Request $request,
        int $product
    ): JsonResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        try {
            $cart = $this->cartService->update(
                $product,
                (int) $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated.',
                'data' => $cart,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a product from the cart.
     */
    public function destroy(int $product): JsonResponse
    {
        try {
            $cart = $this->cartService->remove($product);

            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart.',
                'data' => $cart,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): JsonResponse
    {
        $this->cartService->clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
            'data' => [
                'items' => [],
                'subtotal' => '0.00',
                'item_count' => 0,
            ],
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WishlistResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display the authenticated customer's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with([
                'product.category',
                'product.inventory',
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => WishlistResource::collection($wishlists),
                'count' => $wishlists->count(),
            ],
        ]);
    }

    /**
     * Add a product to the authenticated customer's wishlist.
     */
    public function store(
        Request $request,
        Product $product
    ): JsonResponse {
        /*
         * Do not allow inactive products to be added.
         */
        abort_unless($product->is_active, 404);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        /*
         * Prevent duplicate wishlist entries.
         */
        if (! $wishlist->wasRecentlyCreated) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in your wishlist.',
            ], 422);
        }

        $wishlist->load([
            'product.category',
            'product.inventory',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist.',
            'data' => [
                'item' => new WishlistResource($wishlist),
            ],
        ], 201);
    }

    /**
     * Remove a product from the authenticated customer's wishlist.
     */
    public function destroy(
        Request $request,
        Product $product
    ): JsonResponse {
        $wishlist = $request->user()
            ->wishlists()
            ->where('product_id', $product->id)
            ->first();

        if (! $wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not in your wishlist.',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist.',
        ]);
    }

    /**
     * Clear the authenticated customer's wishlist.
     */
    public function clear(Request $request): JsonResponse
    {
        $deleted = $request->user()
            ->wishlists()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully.',
            'data' => [
                'deleted_count' => $deleted,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * Display approved reviews for a product.
     */
    public function index(
        Request $request,
        Product $product
    ): AnonymousResourceCollection {
        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->with('user')
            ->latest()
            ->paginate(10);

        return ReviewResource::collection($reviews);
    }

    /**
     * Store a new product review.
     *
     * A customer must have a completed order containing
     * the product before they can review it.
     *
     * New reviews require administrator approval.
     */
    public function store(
        Request $request,
        Product $product
    ): ReviewResource|JsonResponse {
        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $userId = $request->user()->id;

        /*
         * Prevent duplicate reviews.
         */
        $existingReview = Review::query()
            ->where('user_id', $userId)
            ->where('reviewable_type', Product::class)
            ->where('reviewable_id', $product->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        /*
         * Verify that the customer purchased this product
         * in a completed order.
         */
        $hasPurchasedProduct = Order::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();

        if (! $hasPurchasedProduct) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products you have purchased and received.',
            ], 422);
        }

        /*
         * Create the review as pending.
         */
        $review = $product->reviews()->create([
            'user_id' => $userId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false,
        ]);

        $review->load([
            'user',
            'reviewable',
        ]);

        return new ReviewResource($review);
    }

    /**
     * Display a customer's own review.
     */
    public function show(
        Request $request,
        Product $product,
        Review $review
    ): ReviewResource {
        $this->ensureProductReview($product, $review);

        abort_unless(
            $review->user_id === $request->user()->id,
            404
        );

        $review->load([
            'user',
            'reviewable',
        ]);

        return new ReviewResource($review);
    }

    /**
     * Update a customer's own review.
     *
     * Updating a review sends it back for administrator approval.
     */
    public function update(
        Request $request,
        Product $product,
        Review $review
    ): ReviewResource {
        $this->ensureProductReview($product, $review);

        abort_unless(
            $review->user_id === $request->user()->id,
            404
        );

        $validated = $request->validate([
            'rating' => [
                'sometimes',
                'integer',
                'between:1,5',
            ],
            'comment' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        /*
         * Any customer modification requires another review.
         */
        $validated['is_approved'] = false;

        $review->update($validated);

        $review->load([
            'user',
            'reviewable',
        ]);

        return new ReviewResource(
            $review->fresh()
        );
    }

    /**
     * Delete a customer's own review.
     */
    public function destroy(
        Request $request,
        Product $product,
        Review $review
    ): JsonResponse {
        $this->ensureProductReview($product, $review);

        abort_unless(
            $review->user_id === $request->user()->id,
            404
        );

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }

    /**
     * Ensure the review belongs to the supplied product.
     */
    protected function ensureProductReview(
        Product $product,
        Review $review
    ): void {
        abort_unless(
            $review->reviewable_type === Product::class
            && (int) $review->reviewable_id === (int) $product->id,
            404
        );
    }
}
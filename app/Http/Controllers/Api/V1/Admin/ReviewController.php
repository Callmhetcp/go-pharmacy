<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display customer reviews.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request
            ->string('status')
            ->trim()
            ->toString();

        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $query = Review::query()
            ->with([
                'user:id,name,email',
                'reviewable',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Moderation filter
        |--------------------------------------------------------------------------
        |
        | ?status=pending
        | ?status=approved
        | ?status=all
        |
        */

        if ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'approved') {
            $query->where('is_approved', true);
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($query) use ($search) {

                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $reviews = $query
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Display a single review.
     */
    public function show(Review $review): JsonResponse
    {
        $review->load([
            'user:id,name,email',
            'reviewable',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'review' => $review,
            ],
        ]);
    }

    /**
     * Approve a customer review.
     */
    public function approve(Review $review): JsonResponse
    {
        if ($review->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This review is already approved.',
            ], 422);
        }

        $review->update([
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review approved successfully.',
            'data' => [
                'review' => $review->fresh([
                    'user:id,name,email',
                    'reviewable',
                ]),
            ],
        ]);
    }

    /**
     * Reject/unapprove a customer review.
     *
     * The review remains in the database but is no longer
     * publicly visible as an approved review.
     */
    public function reject(Review $review): JsonResponse
    {
        if (! $review->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This review is already pending approval.',
            ], 422);
        }

        $review->update([
            'is_approved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review has been rejected.',
            'data' => [
                'review' => $review->fresh([
                    'user:id,name,email',
                    'reviewable',
                ]),
            ],
        ]);
    }

    /**
     * Delete a review permanently.
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}
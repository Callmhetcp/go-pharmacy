<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Display customer reviews.
     */
    public function index(Request $request): Response
    {
        $query = Review::query()
            ->with([
                'user:id,name,email',
                'reviewable',
            ])
            ->latest();

        /*
         * Optional moderation filter.
         *
         * ?status=pending
         * ?status=approved
         * ?status=all
         */
        $status = $request->input('status', 'all');

        if ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'approved') {
            $query->where('is_approved', true);
        }

        $reviews = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    /**
     * Display a single review.
     */
    public function show(Review $review): Response
    {
        $review->load([
            'user:id,name,email',
            'reviewable',
        ]);

        return Inertia::render('Admin/Reviews/Show', [
            'review' => $review,
        ]);
    }

    /**
     * Approve a customer review.
     */
    public function approve(Review $review): RedirectResponse
    {
        if ($review->is_approved) {
            return back()->with(
                'error',
                'This review is already approved.'
            );
        }

        $review->update([
            'is_approved' => true,
        ]);

        return redirect()
            ->route('admin.reviews.index')
            ->with(
                'success',
                'Review approved successfully.'
            );
    }

    /**
     * Reject/unapprove a customer review.
     *
     * The review is retained in the database but will no longer
     * appear among publicly approved reviews.
     */
    public function reject(Review $review): RedirectResponse
    {
        if (! $review->is_approved) {
            return back()->with(
                'error',
                'This review is already pending approval.'
            );
        }

        $review->update([
            'is_approved' => false,
        ]);

        return redirect()
            ->route('admin.reviews.index')
            ->with(
                'success',
                'Review has been rejected.'
            );
    }

    /**
     * Delete a review permanently.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with(
                'success',
                'Review deleted successfully.'
            );
    }
}

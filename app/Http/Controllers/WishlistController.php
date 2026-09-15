<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    /**
     * Display the authenticated customer's wishlist.
     */
    public function index(Request $request): Response
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with([
                'product.category',
                'product.inventory',
            ])
            ->latest()
            ->get();

        return Inertia::render('Wishlist/Index', [
            'wishlist' => $wishlists->map(function ($wishlist) {
                return [
                    'id' => $wishlist->id,

                    'product' => [
                        'id' => $wishlist->product?->id,
                        'name' => $wishlist->product?->name,
                        'slug' => $wishlist->product?->slug,
                        'sku' => $wishlist->product?->sku,
                        'brand' => $wishlist->product?->brand,
                        'generic_name' => $wishlist->product?->generic_name,
                        'price' => $wishlist->product?->price,
                        'sale_price' => $wishlist->product?->sale_price,
                        'image' => $wishlist->product?->image_url,
                        'requires_prescription' =>
                            (bool) $wishlist->product?->requires_prescription,
                        'is_active' =>
                            (bool) $wishlist->product?->is_active,
                    ],

                    'created_at' =>
                        $wishlist->created_at?->toISOString(),
                ];
            })->values(),
        ]);
    }
}
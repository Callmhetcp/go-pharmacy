<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Shop
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with(['category', 'inventory'])
            ->where('is_active', true)

            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', function ($query) use ($request) {
                    $query->where('slug', $request->category);
                });
            })

            ->when($request->filled('prescription'), function ($query) use ($request) {
                if ($request->prescription === 'yes') {
                    $query->where('requires_prescription', true);
                }

                if ($request->prescription === 'no') {
                    $query->where('requires_prescription', false);
                }
            })

            ->when($request->filled('sort'), function ($query) use ($request) {
                match ($request->sort) {
                    'price_low' => $query->orderBy('price', 'asc'),
                    'price_high' => $query->orderBy('price', 'desc'),
                    'name' => $query->orderBy('name', 'asc'),
                    'newest' => $query->latest(),
                    default => $query->latest(),
                };
            })

            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'prescription' => $request->prescription,
                'sort' => $request->sort,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Details
    |--------------------------------------------------------------------------
    */

    public function show(Product $product): Response
    {
        $product->load([
            'category',
            'inventory',
        ]);

        $relatedProducts = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with([
                'category',
                'inventory',
            ])
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('Shop/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
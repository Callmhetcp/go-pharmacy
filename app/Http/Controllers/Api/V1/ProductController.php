<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Display active products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with([
                'category',
                'inventory',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        |
        | Accepts either:
        | ?category=medicines
        | ?category=2
        |
        */

        if ($request->filled('category')) {
            $category = $request->input('category');

            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category)
                    ->orWhere('id', $category);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Featured Filter
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        /*
        |--------------------------------------------------------------------------
        | Prescription Filter
        |--------------------------------------------------------------------------
        |
        | ?prescription_required=true
        |
        */

        if ($request->has('prescription_required')) {
            $query->where(
                'requires_prescription',
                $request->boolean('prescription_required')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Price Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_price')) {
            $query->where(
                'price',
                '>=',
                (float) $request->input('min_price')
            );
        }

        if ($request->filled('max_price')) {
            $query->where(
                'price',
                '<=',
                (float) $request->input('max_price')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Stock Filter
        |--------------------------------------------------------------------------
        |
        | ?in_stock=true
        |
        */

        if ($request->boolean('in_stock')) {
            $query->whereHas('inventory', function ($q) {
                $q->where('available_quantity', '>', 0);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;

            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;

            case 'newest':
                $query->latest();
                break;

            default:
                $query->latest('id');
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max((int) $request->input('per_page', 12), 1),
            48
        );

        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Display a single active product.
     */
    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category',
            'inventory',
        ]);

        return new ProductResource($product);
    }

   /**
     * Display related products.
     */
    public function related(Product $product): AnonymousResourceCollection
    {
        abort_unless($product->is_active, 404);

        $products = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->with([
                'category',
                'inventory',
            ])
            ->orderByRaw(
                'CASE
                    WHEN category_id = ? THEN 1
                    WHEN brand IS NOT NULL AND brand = ? THEN 2
                    WHEN generic_name IS NOT NULL AND generic_name = ? THEN 3
                    WHEN is_featured = 1 THEN 4
                    ELSE 5
                END',
                [
                    $product->category_id,
                    $product->brand,
                    $product->generic_name,
                ]
            )
            ->latest('id')
            ->limit(8)
            ->get();

        return ProductResource::collection($products);
    }
}
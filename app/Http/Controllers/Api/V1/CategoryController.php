<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Display all active categories.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Display active products belonging to a category.
     */
    public function products(Category $category): AnonymousResourceCollection
    {
        abort_unless($category->is_active, 404);

        $products = $category->products()
            ->where('is_active', true)
            ->with([
                'category',
                'inventory',
            ])
            ->latest('id')
            ->paginate(12);

        return ProductResource::collection($products);
    }
}
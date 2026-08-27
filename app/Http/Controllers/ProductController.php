<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display all available products.
     */
    public function index(): Response
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Shop', [
            'products' => $products,
        ]);
    }

    /**
     * Display a single product.
     */
   public function show(Product $product): Response
{
    abort_unless($product->is_active, 404);

    $product->load('category:id,name,slug');

    return Inertia::render('Shop/Show', [
        'product' => $product,
    ]);
}
}
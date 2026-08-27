<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with([
                'category:id,name,slug',
                'supplier:id,name,company_name',
                'inventory:id,product_id,quantity,reserved_quantity,minimum_stock',
            ])
            ->withCount('inventoryTransactions')
            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            );

        return ProductResource::collection($products);
    }

    /**
     * Display a single product.
     */
    public function show(Product $product): ProductResource
    {
        $product->load([
            'category:id,name,slug',
            'supplier:id,name,company_name',
            'inventory:id,product_id,quantity,reserved_quantity,minimum_stock',
        ]);

        return new ProductResource($product);
    }

    /**
     * Store a product.
     */
    public function store(Request $request): ProductResource
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,slug',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,sku',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,barcode',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'brand' => [
                'nullable',
                'string',
                'max:255',
            ],

            'generic_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'dosage_form' => [
                'nullable',
                'string',
                'max:255',
            ],

            'strength' => [
                'nullable',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cost_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'base_unit' => [
                'nullable',
                'string',
                'max:255',
            ],

            'selling_unit' => [
                'nullable',
                'string',
                'max:255',
            ],

            'units_per_selling_unit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'allow_partial_sale' => [
                'sometimes',
                'boolean',
            ],

            'packaging_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'minimum_stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'requires_prescription' => [
                'sometimes',
                'boolean',
            ],

            'image' => [
                'nullable',
                'image',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate slug
        |--------------------------------------------------------------------------
        |
        | The database requires a non-null unique slug.
        | Generate one automatically when the client does not provide it.
        |
        */

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
        |--------------------------------------------------------------------------
        | Product defaults
        |--------------------------------------------------------------------------
        */

        $validated['base_unit'] = $validated['base_unit'] ?? 'piece';
        $validated['selling_unit'] = $validated['selling_unit'] ?? 'piece';

        $validated['units_per_selling_unit'] =
            $validated['units_per_selling_unit'] ?? 1;

        $validated['allow_partial_sale'] =
            $request->boolean('allow_partial_sale');

        $validated['is_active'] =
            $request->boolean('is_active', true);

        $validated['is_featured'] =
            $request->boolean('is_featured');

        $validated['requires_prescription'] =
            $request->boolean('requires_prescription');

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Inventory threshold
        |--------------------------------------------------------------------------
        |
        | minimum_stock belongs to inventory, not the product record.
        |
        */

        $minimumStock = $validated['minimum_stock'] ?? 0;

        unset($validated['minimum_stock']);

        /*
        |--------------------------------------------------------------------------
        | Create product
        |--------------------------------------------------------------------------
        */

        $product = Product::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Create inventory record
        |--------------------------------------------------------------------------
        */

        $product->inventory()->create([
            'quantity' => 0,
            'reserved_quantity' => 0,
            'minimum_stock' => $minimumStock,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load relationships
        |--------------------------------------------------------------------------
        */

        $product->load([
            'category:id,name,slug',
            'supplier:id,name,company_name',
            'inventory:id,product_id,quantity,reserved_quantity,minimum_stock',
        ]);

        return new ProductResource($product);
    }

    /**
     * Update a product.
     */
    public function update(
        Request $request,
        Product $product
    ): ProductResource {
        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id',
            ],

            'supplier_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'unique:products,slug,' . $product->id,
            ],

            'sku' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'unique:products,sku,' . $product->id,
            ],

            'barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'unique:products,barcode,' . $product->id,
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'short_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'brand' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'generic_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'dosage_form' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'strength' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'cost_price' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'base_unit' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'selling_unit' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'units_per_selling_unit' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'allow_partial_sale' => [
                'sometimes',
                'boolean',
            ],

            'packaging_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'minimum_stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'requires_prescription' => [
                'sometimes',
                'boolean',
            ],

            'image' => [
                'sometimes',
                'nullable',
                'image',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate slug when name changes but slug isn't supplied
        |--------------------------------------------------------------------------
        */

        if (
            $request->has('name') &&
            !$request->has('slug')
        ) {
            $validated['slug'] = $this->generateUniqueSlug(
                null,
                $validated['name'],
                $product->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Boolean fields
        |--------------------------------------------------------------------------
        */

        if ($request->has('is_active')) {
            $validated['is_active'] =
                $request->boolean('is_active');
        }

        if ($request->has('is_featured')) {
            $validated['is_featured'] =
                $request->boolean('is_featured');
        }

        if ($request->has('requires_prescription')) {
            $validated['requires_prescription'] =
                $request->boolean('requires_prescription');
        }

        if ($request->has('allow_partial_sale')) {
            $validated['allow_partial_sale'] =
                $request->boolean('allow_partial_sale');
        }

        /*
        |--------------------------------------------------------------------------
        | Inventory threshold
        |--------------------------------------------------------------------------
        */

        $minimumStock = $validated['minimum_stock'] ?? null;

        unset($validated['minimum_stock']);

        /*
        |--------------------------------------------------------------------------
        | Update product
        |--------------------------------------------------------------------------
        */

        $product->update($validated);

        /*
        |--------------------------------------------------------------------------
        | Update inventory threshold
        |--------------------------------------------------------------------------
        */

        if ($minimumStock !== null) {
            $product->inventory()->update([
                'minimum_stock' => $minimumStock,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reload
        |--------------------------------------------------------------------------
        */

        $product->load([
            'category:id,name,slug',
            'supplier:id,name,company_name',
            'inventory:id,product_id,quantity,reserved_quantity,minimum_stock',
        ]);

        return new ProductResource($product->fresh());
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product): JsonResponse
    {
        if ($product->inventoryTransactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This product cannot be deleted because it has inventory transaction records.',
            ], 422);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Generate a unique product slug.
     */
    private function generateUniqueSlug(
        ?string $slug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($slug ?: $name);

        if ($baseSlug === '') {
            $baseSlug = 'product';
        }

        $candidate = $baseSlug;
        $counter = 2;

        while (
            Product::query()
                ->where('slug', $candidate)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdvertisementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Advertisements
    |--------------------------------------------------------------------------
    */

    public function index(): Response
    {
        $advertisements = Advertisement::query()
            ->with('product:id,name,slug,image')
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Advertisements/Index', [
            'advertisements' => $advertisements,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Advertisement
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'price',
                'image',
            ]);

        return Inertia::render('Admin/Advertisements/Create', [
            'products' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Advertisement
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'product_id' => [
                'required',
                'exists:products,id',
            ],

            'button_text' => [
                'required',
                'string',
                'max:50',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload Advertisement Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('advertisements', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Values
        |--------------------------------------------------------------------------
        */

        $validated['is_active'] = $request->boolean('is_active');

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Create Advertisement
        |--------------------------------------------------------------------------
        */

        Advertisement::create($validated);

        return redirect()
            ->route('admin.advertisements.index')
            ->with(
                'success',
                'Advertisement created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Advertisement
    |--------------------------------------------------------------------------
    */

    public function edit(
        Advertisement $advertisement
    ): Response {
        $advertisement->load(
            'product:id,name,slug,price,image'
        );

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'price',
                'image',
            ]);

        return Inertia::render('Admin/Advertisements/Edit', [
            'advertisement' => $advertisement,
            'products' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Advertisement
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Advertisement $advertisement
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'product_id' => [
                'required',
                'exists:products,id',
            ],

            'button_text' => [
                'required',
                'string',
                'max:50',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize Values
        |--------------------------------------------------------------------------
        */

        $validated['is_active'] = $request->boolean('is_active');

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Replace Image Only If A New Image Was Uploaded
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            if ($advertisement->image) {
                Storage::disk('public')->delete(
                    $advertisement->image
                );
            }

            $validated['image'] = $request
                ->file('image')
                ->store('advertisements', 'public');
        } else {
            /*
            | Keep the existing image.
            | Never send image = null to the database.
            */
            unset($validated['image']);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Advertisement
        |--------------------------------------------------------------------------
        */

        $advertisement->update($validated);

        return redirect()
            ->route('admin.advertisements.index')
            ->with(
                'success',
                'Advertisement updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Advertisement
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Advertisement $advertisement
    ): RedirectResponse {
        if ($advertisement->image) {
            Storage::disk('public')->delete(
                $advertisement->image
            );
        }

        $advertisement->delete();

        return redirect()
            ->route('admin.advertisements.index')
            ->with(
                'success',
                'Advertisement deleted successfully.'
            );
    }
}
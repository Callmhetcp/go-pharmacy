<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display inventory.
     */
    public function index(Request $request): Response
    {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $inventories = Inventory::query()
            ->with('product.category')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery
                        ->where(function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Inventory/Index', [
            'inventory' => $inventories,

            'filters' => [
                'search' => $search,
            ],
        ]);
    }


    /**
     * Show stock adjustment form.
     */
    public function create(): Response
    {
        $products = Product::query()
            ->with(['category', 'inventory'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'category_id',
                'name',
                'sku',
                'barcode',
            ]);

        return Inertia::render('Admin/Inventory/Create', [
            'products' => $products,
        ]);
    }


    /**
     * Add or remove stock.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'type' => [
                'required',
                'in:purchase,return,adjustment,damaged,expired,correction',
            ],

            'quantity' => [
                'required',
                'integer',
                'not_in:0',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'minimum_stock' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Get or create inventory
            |--------------------------------------------------------------------------
            */

            $inventory = Inventory::firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'minimum_stock' => $validated['minimum_stock'] ?? 0,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Lock inventory row
            |--------------------------------------------------------------------------
            |
            | Prevents two administrators/processes from modifying the same
            | stock quantity at the same time.
            |
            */

            $inventory = Inventory::query()
                ->whereKey($inventory->id)
                ->lockForUpdate()
                ->first();

            $before = $inventory->quantity;

            $quantity = abs((int) $validated['quantity']);

            /*
            |--------------------------------------------------------------------------
            | Determine stock movement
            |--------------------------------------------------------------------------
            */

            $increaseTypes = [
                'purchase',
                'return',
            ];

            $decreaseTypes = [
                'damaged',
                'expired',
            ];

            if (in_array($validated['type'], $increaseTypes, true)) {

                $change = $quantity;

            } elseif (in_array($validated['type'], $decreaseTypes, true)) {

                $change = -$quantity;

            } else {

                /*
                | adjustment / correction
                |
                | For these types the administrator controls the direction
                | using the submitted quantity.
                |
                | Positive = add stock
                | Negative = remove stock
                */

                $change = (int) $validated['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate new quantity
            |--------------------------------------------------------------------------
            */

            $after = $before + $change;

            /*
            |--------------------------------------------------------------------------
            | Prevent negative inventory
            |--------------------------------------------------------------------------
            */

            if ($after < 0) {
                abort(
                    422,
                    'Inventory quantity cannot be less than zero.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent removing more stock than is available
            |--------------------------------------------------------------------------
            */

            if (
                $change < 0 &&
                abs($change) > $inventory->quantity
            ) {
                abort(
                    422,
                    'There is not enough stock available for this adjustment.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update inventory
            |--------------------------------------------------------------------------
            */

            $inventory->update([
                'quantity' => $after,

                'minimum_stock' => array_key_exists(
                    'minimum_stock',
                    $validated
                )
                    ? ($validated['minimum_stock'] ?? 0)
                    : $inventory->minimum_stock,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Record transaction
            |--------------------------------------------------------------------------
            */

            InventoryTransaction::create([
                'product_id' => $inventory->product_id,
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),

                'type' => $validated['type'],

                'quantity' => $change,

                'quantity_before' => $before,
                'quantity_after' => $after,

                'reference' => $validated['reference'] ?? null,

                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.inventory.index')
            ->with(
                'success',
                'Inventory updated successfully.'
            );
    }


    /**
     * Show inventory details and transaction history.
     */
    public function show(Inventory $inventory): Response
    {
        $inventory->load([
            'product.category',
        ]);

        $transactions = $inventory
            ->transactions()
            ->with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Inventory/Show', [
            'inventory' => $inventory,
            'transactions' => $transactions,
        ]);
    }
}

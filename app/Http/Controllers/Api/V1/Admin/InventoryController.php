<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display paginated inventory.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $inventories = Inventory::query()
            ->with([
                'product.category',
            ])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $inventories,
        ]);
    }

    /**
     * Display inventory details and transaction history.
     */
    public function show(Inventory $inventory): JsonResponse
    {
        $inventory->load([
            'product.category',
        ]);

        $transactions = $inventory
            ->transactions()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => [
                'inventory' => $inventory,
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * Add or remove stock.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'type' => [
                'required',
                'string',
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

        $result = DB::transaction(function () use ($validated) {

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
            */

            $inventory = Inventory::query()
                ->whereKey($inventory->id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = (int) $inventory->quantity;

            $quantity = abs(
                (int) $validated['quantity']
            );

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
                |--------------------------------------------------------------------------
                | adjustment / correction
                |--------------------------------------------------------------------------
                |
                | Positive quantity = add stock
                | Negative quantity = remove stock
                |
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
            | Prevent removing more stock than available
            |--------------------------------------------------------------------------
            */

            if (
                $change < 0 &&
                abs($change) > $before
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
            | Record inventory transaction
            |--------------------------------------------------------------------------
            */

            $transaction = InventoryTransaction::create([
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

            return [
                'inventory' => $inventory->fresh([
                    'product.category',
                ]),

                'transaction' => $transaction->load(
                    'user:id,name,email'
                ),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully.',
            'data' => $result,
        ], 201);
    }
}

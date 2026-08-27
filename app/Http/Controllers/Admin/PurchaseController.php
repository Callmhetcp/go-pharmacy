<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    /**
     * Display purchases.
     */
    public function index(Request $request): Response
    {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        $status = $request
            ->string('status')
            ->trim()
            ->toString();

        $purchases = Purchase::query()
            ->with([
                'supplier',
                'user',
            ])
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'company_name',
                                    'like',
                                    "%{$search}%"
                                );
                        });
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Purchases/Index', [
            'purchases' => $purchases,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],

            'statuses' => [
                'draft',
                'ordered',
                'received',
                'cancelled',
            ],
        ]);
    }

    /**
     * Show create purchase form.
     */
    public function create(): Response
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'company_name',
            ]);

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'category_id',
                'name',
                'sku',
                'barcode',
                'price',
            ]);

        return Inertia::render('Admin/Purchases/Create', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    /**
     * Store a purchase.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
                'unique:purchases,reference',
            ],

            'purchase_date' => [
                'required',
                'date',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:draft,ordered,received,cancelled',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.batch_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.expiry_date' => [
                'nullable',
                'date',
            ],
        ]);

        $purchase = DB::transaction(function () use ($validated) {

            $subtotal = collect($validated['items'])
                ->sum(function ($item) {
                    return (float) $item['quantity']
                        * (float) $item['unit_cost'];
                });

            $discount = (float) ($validated['discount'] ?? 0);

            if ($discount > $subtotal) {
                abort(
                    422,
                    'Discount cannot be greater than the purchase subtotal.'
                );
            }

            $totalAmount = $subtotal - $discount;

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'reference' => $validated['reference'],
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $quantity * $unitCost,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,

                    /*
                     * Stock does not enter inventory until the
                     * purchase is actually received.
                     */
                    'remaining_quantity' => 0,
                    'status' => 'active',
                ]);
            }

            return $purchase;
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with(
                'success',
                'Purchase order created successfully.'
            );
    }

    /**
     * Display a purchase.
     */
    public function show(Purchase $purchase): Response
    {
        $purchase->load([
            'supplier',
            'user',
            'items.product.category',
        ]);

        return Inertia::render('Admin/Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Receive a purchase and add the purchased quantities
     * to inventory.
     *
     * Receiving also initializes the purchase batch:
     *
     * remaining_quantity = received quantity
     * status = active
     */
    public function receive(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        if ($purchase->status !== 'ordered') {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with(
                    'error',
                    'Only ordered purchases can be received.'
                );
        }

        $purchase->load('items.product');

        if ($purchase->items->isEmpty()) {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with(
                    'error',
                    'This purchase has no items and cannot be received.'
                );
        }

        DB::transaction(function () use ($purchase) {

            /*
             * Lock the purchase so two requests cannot
             * receive it at the same time.
             */
            $lockedPurchase = Purchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPurchase->status !== 'ordered') {
                abort(
                    422,
                    'This purchase has already been received or is no longer available for receiving.'
                );
            }

            $lockedPurchase->load('items.product');

            foreach ($lockedPurchase->items as $item) {

                /*
                 * Find the inventory record for the product.
                 */
                $inventory = Inventory::query()
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                /*
                 * Create inventory if this is the first
                 * purchase of the product.
                 */
                if (! $inventory) {
                    $inventory = Inventory::create([
                        'product_id' => $item->product_id,
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'minimum_stock' => 0,
                    ]);

                    /*
                     * Lock the newly created inventory record.
                     */
                    $inventory = Inventory::query()
                        ->whereKey($inventory->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                $quantityBefore = (int) $inventory->quantity;

                $quantityReceived = (int) $item->quantity;

                $quantityAfter = $quantityBefore + $quantityReceived;

                /*
                 * Add the received quantity to physical inventory.
                 */
                $inventory->update([
                    'quantity' => $quantityAfter,
                ]);

                /*
                 * Initialize the purchase batch.
                 *
                 * This is what allows the system to know how
                 * much stock from this specific purchase remains
                 * available for return or disposal.
                 */
                $item->update([
                    'remaining_quantity' => $quantityReceived,
                    'status' => 'active',
                ]);

                /*
                 * Create inventory audit transaction.
                 */
                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'inventory_id' => $inventory->id,
                    'user_id' => Auth::id(),

                    'type' => 'purchase',

                    'quantity' => $quantityReceived,

                    'quantity_before' => $quantityBefore,

                    'quantity_after' => $quantityAfter,

                    'reference' => $lockedPurchase->reference,

                    'notes' => sprintf(
                        'Stock received from purchase order %s. Batch: %s. Expiry date: %s.',
                        $lockedPurchase->reference,
                        $item->batch_number ?: 'N/A',
                        $item->expiry_date?->format('Y-m-d') ?: 'N/A'
                    ),
                ]);
            }

            /*
             * Mark the purchase itself as received.
             */
            $lockedPurchase->update([
                'status' => 'received',
            ]);
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with(
                'success',
                'Purchase received successfully and inventory has been updated.'
            );
    }

    /**
     * Show the edit purchase form.
     */
    public function edit(
        Purchase $purchase
    ): Response|RedirectResponse {
        if (in_array(
            $purchase->status,
            ['received', 'cancelled'],
            true
        )) {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with(
                    'error',
                    'This purchase cannot be edited because it is already received or cancelled.'
                );
        }

        $purchase->load('items');

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'company_name',
            ]);

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'category_id',
                'name',
                'sku',
                'barcode',
                'price',
            ]);

        return Inertia::render('Admin/Purchases/Edit', [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    /**
     * Update a purchase.
     */
    public function update(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        if (in_array(
            $purchase->status,
            ['received', 'cancelled'],
            true
        )) {
            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with(
                    'error',
                    'This purchase cannot be edited because it is already received or cancelled.'
                );
        }

        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
                'unique:purchases,reference,' . $purchase->id,
            ],

            'purchase_date' => [
                'required',
                'date',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:draft,ordered',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.batch_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'items.*.expiry_date' => [
                'nullable',
                'date',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $purchase
        ) {
            $subtotal = collect($validated['items'])
                ->sum(function ($item) {
                    return (float) $item['quantity']
                        * (float) $item['unit_cost'];
                });

            $discount = (float) ($validated['discount'] ?? 0);

            if ($discount > $subtotal) {
                abort(
                    422,
                    'Discount cannot be greater than the purchase subtotal.'
                );
            }

            $totalAmount = $subtotal - $discount;

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'reference' => $validated['reference'],
                'purchase_date' => $validated['purchase_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            /*
             * Replace purchase items.
             *
             * This is safe because received purchases cannot
             * reach this method.
             */
            $purchase->items()->delete();

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $quantity * $unitCost,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,

                    /*
                     * No inventory has entered the pharmacy yet.
                     */
                    'remaining_quantity' => 0,
                    'status' => 'active',
                ]);
            }
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with(
                'success',
                'Purchase order updated successfully.'
            );
    }
}
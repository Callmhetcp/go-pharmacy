<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService
    ) {
    }

    /**
     * POS screen.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/POS/Index', [
            'paymentMethods' => [
                [
                    'value' => 'cash',
                    'label' => 'Cash',
                ],
                [
                    'value' => 'transfer',
                    'label' => 'Bank Transfer',
                ],
                [
                    'value' => 'card',
                    'label' => 'Card',
                ],
            ],
        ]);
    }

    /**
     * Search products.
     */
    public function products(Request $request): JsonResponse
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        if ($search === '') {
            return response()->json([
                'products' => [],
            ]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->with('inventory')
            ->where(function ($query) use ($search) {
                $query
                    ->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'sku',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'barcode',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'generic_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'brand',
                        'like',
                        "%{$search}%"
                    );
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function (Product $product) {
                $inventory = $product->inventory;

                $availableQuantity = $inventory
                    ? max(
                        0,
                        (int) $inventory->quantity
                        - (int) $inventory->reserved_quantity
                    )
                    : 0;

                $unitsPerSellingUnit = max(
                    1,
                    (int) $product->units_per_selling_unit
                );

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'brand' => $product->brand,
                    'generic_name' => $product->generic_name,

                    'price' => (float) $product->price,

                    'selling_unit' =>
                        $product->sellingUnitLabel(),

                    'base_unit' =>
                        $product->baseUnitLabel(),

                    'units_per_selling_unit' =>
                        $unitsPerSellingUnit,

                    'requires_prescription' =>
                        (bool) $product->requires_prescription,

                    'available_base_quantity' =>
                        $availableQuantity,

                    'available_selling_quantity' =>
                        intdiv(
                            $availableQuantity,
                            $unitsPerSellingUnit
                        ),

                    'image_url' =>
                        $product->image_url,
                ];
            });

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Search customers.
     */
    public function customers(Request $request): JsonResponse
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        if ($search === '') {
            return response()->json([
                'customers' => [],
            ]);
        }

        $customers = User::query()
            ->where('is_admin', false)
            ->where(function ($query) use ($search) {
                $query
                    ->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        "%{$search}%"
                    );
            })
            ->orderBy('name')
            ->limit(15)
            ->get([
                'id',
                'name',
                'email',
                'phone',
            ]);

        return response()->json([
            'customers' => $customers,
        ]);
    }

    /**
     * POS sales history.
     */
    public function history(Request $request): Response
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $paymentMethod = $request->input(
            'payment_method'
        );

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Order::query()
            ->where(
                'order_number',
                'like',
                'GP-POS-%'
            )
            ->with([
                'payments' => function ($query) {
                    $query
                        ->where(
                            'status',
                            'successful'
                        )
                        ->latest('paid_at');
                },
                'user',
                'cashier',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'order_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'receipt_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'customer_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'customer_phone',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'customer_email',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Method
        |--------------------------------------------------------------------------
        */

        if (
            $paymentMethod !== null
            && $paymentMethod !== ''
        ) {
            $query->whereHas(
                'payments',
                function ($query) use ($paymentMethod) {
                    $query
                        ->where(
                            'status',
                            'successful'
                        )
                        ->where(
                            'payment_method',
                            $paymentMethod
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {
            $query->whereDate(
                'created_at',
                '>=',
                $dateFrom
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if ($dateTo) {
            $query->whereDate(
                'created_at',
                '<=',
                $dateTo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summaryQuery = clone $query;

        $summary = [
            'total_sales' =>
                (clone $summaryQuery)->count(),

            'total_amount' =>
                (float) (
                    (clone $summaryQuery)->sum('total')
                ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Paginated Sales
        |--------------------------------------------------------------------------
        */

        $sales = $query
            ->paginate(20)
            ->withQueryString()
            ->through(function (Order $order) {
                $payment = $order->payments->first();

                return [
                    'id' => $order->id,

                    'order_number' =>
                        $order->order_number,

                    'receipt_number' =>
                        $order->receipt_number,

                    /*
                    |--------------------------------------------------------------------------
                    | Customer
                    |--------------------------------------------------------------------------
                    */

                    'customer_name' =>
                        $order->customer_name
                        ?? 'Walk-in Customer',

                    'customer_phone' =>
                        $order->customer_phone,

                    'customer_email' =>
                        $order->customer_email,

                    /*
                    |--------------------------------------------------------------------------
                    | Cashier
                    |--------------------------------------------------------------------------
                    */

                    'cashier' => $order->cashier
                        ? [
                            'id' =>
                                $order->cashier->id,

                            'name' =>
                                $order->cashier->name,

                            'email' =>
                                $order->cashier->email,
                        ]
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Financials
                    |--------------------------------------------------------------------------
                    */

                    'subtotal' =>
                        (float) $order->subtotal,

                    'discount' =>
                        (float) $order->discount,

                    'total' =>
                        (float) $order->total,

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    'status' =>
                        $order->status,

                    'payment_status' =>
                        $order->payment_status,

                    /*
                    |--------------------------------------------------------------------------
                    | Payment
                    |--------------------------------------------------------------------------
                    */

                    'payment_method' =>
                        $payment?->payment_method,

                    'payment_reference' =>
                        $payment?->payment_reference,

                    /*
                    |--------------------------------------------------------------------------
                    | Date
                    |--------------------------------------------------------------------------
                    */

                    'created_at' =>
                        $order->created_at?->toISOString(),

                    'created_at_formatted' =>
                        $order->created_at
                            ?->format('d M Y, h:i A'),
                ];
            });

        return Inertia::render(
            'Admin/POS/History',
            [
                'sales' => $sales,

                'filters' => [
                    'search' => $search,

                    'payment_method' =>
                        $paymentMethod,

                    'date_from' =>
                        $dateFrom,

                    'date_to' =>
                        $dateTo,
                ],

                'summary' => $summary,

                'paymentMethods' => [
                    [
                        'value' => 'cash',
                        'label' => 'Cash',
                    ],
                    [
                        'value' => 'transfer',
                        'label' => 'Bank Transfer',
                    ],
                    [
                        'value' => 'card',
                        'label' => 'Card',
                    ],
                ],
            ]
        );
    }

    /**
     * Complete POS sale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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

            'customer_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'customer_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'customer_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'customer_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'in:cash,transfer,card',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {
            $customer = null;

            if (! empty($validated['customer_id'])) {
                $customer = User::query()
                    ->where('is_admin', false)
                    ->findOrFail(
                        $validated['customer_id']
                    );
            }

            $order = $this->posService->createSale([
                'items' =>
                    $validated['items'],

                'customer' => $customer
                    ? [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]
                    : null,

                'customer_name' =>
                    $validated['customer_name']
                    ?? 'Walk-in Customer',

                'customer_email' =>
                    $validated['customer_email']
                    ?? null,

                'customer_phone' =>
                    $validated['customer_phone']
                    ?? null,

                'discount' =>
                    $validated['discount']
                    ?? 0,

                'payment_method' =>
                    $validated['payment_method'],

                'notes' =>
                    $validated['notes']
                    ?? null,
            ]);

            return redirect()
                ->route(
                    'admin.pos.receipt',
                    $order
                )
                ->with(
                    'success',
                    'POS sale completed successfully.'
                );
        } catch (Throwable $e) {
            Log::error(
                'POS sale failed.',
                [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->withErrors([
                    'pos' => $e->getMessage(),
                ]);
        }
    }

    /**
     * POS receipt.
     */
    public function receipt(Order $order): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Only allow POS orders
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | GP-POS- identifies the POS order.
        | GP-RCPT- identifies the receipt number.
        |
        */

        abort_unless(
            str_starts_with(
                $order->order_number,
                'GP-POS-'
            ),
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Load receipt relationships
        |--------------------------------------------------------------------------
        */

        $order->load([
            'items.product',
            'payments',
            'user',
            'cashier',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Receipt
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Admin/POS/Receipt',
            [
                'order' => $order,

                'settings' =>
                    $this->posService
                        ->receiptSettings(),
            ]
        );
    }

    /**
     * POS sale details.
     */
    public function show(Order $order): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Only allow POS orders
        |--------------------------------------------------------------------------
        */

        abort_unless(
            str_starts_with(
                $order->order_number,
                'GP-POS-'
            ),
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Load relationships
        |--------------------------------------------------------------------------
        |
        | user    = customer
        | cashier = logged-in admin/cashier
        |
        */

        $order->load([
            'user',
            'cashier',
            'items.product',
            'payments',
        ]);

        return Inertia::render(
            'Admin/POS/Show',
            [
                'order' => [
                    'id' =>
                        $order->id,

                    'order_number' =>
                        $order->order_number,

                    'receipt_number' =>
                        $order->receipt_number,

                    /*
                    |--------------------------------------------------------------------------
                    | Customer
                    |--------------------------------------------------------------------------
                    */

                    'customer_name' =>
                        $order->customer_name
                        ?: 'Walk-in Customer',

                    'customer_email' =>
                        $order->customer_email,

                    'customer_phone' =>
                        $order->customer_phone,

                    /*
                    |--------------------------------------------------------------------------
                    | Financials
                    |--------------------------------------------------------------------------
                    */

                    'subtotal' =>
                        (float) $order->subtotal,

                    'delivery_fee' =>
                        (float) $order->delivery_fee,

                    'discount' =>
                        (float) $order->discount,

                    'total' =>
                        (float) $order->total,

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    'status' =>
                        $order->status,

                    'payment_status' =>
                        $order->payment_status,

                    'notes' =>
                        $order->notes,

                    'created_at' =>
                        $order->created_at,

                    /*
                    |--------------------------------------------------------------------------
                    | Customer Account
                    |--------------------------------------------------------------------------
                    */

                    'customer' => $order->user
                        ? [
                            'id' =>
                                $order->user->id,

                            'name' =>
                                $order->user->name,

                            'email' =>
                                $order->user->email,

                            'phone' =>
                                $order->user->phone,
                        ]
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Cashier
                    |--------------------------------------------------------------------------
                    */

                    'cashier' => $order->cashier
                        ? [
                            'id' =>
                                $order->cashier->id,

                            'name' =>
                                $order->cashier->name,

                            'email' =>
                                $order->cashier->email,
                        ]
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Items
                    |--------------------------------------------------------------------------
                    */

                    'items' => $order->items
                        ->map(function ($item) {
                            return [
                                'id' =>
                                    $item->id,

                                'product_id' =>
                                    $item->product_id,

                                'product_name' =>
                                    $item->product_name
                                    ?? $item->product?->name
                                    ?? 'Product',

                                'sku' =>
                                    $item->product_sku
                                    ?? $item->product?->sku,

                                'quantity' =>
                                    $item->quantity,

                                'selling_unit' =>
                                    $item->selling_unit,

                                'base_unit' =>
                                    $item->base_unit,

                                'base_quantity' =>
                                    $item->base_quantity,

                                'unit_price' =>
                                    (float) $item->unit_price,

                                'subtotal' =>
                                    (float) $item->subtotal,
                            ];
                        })
                        ->values(),

                    /*
                    |--------------------------------------------------------------------------
                    | Payments
                    |--------------------------------------------------------------------------
                    */

                    'payments' => $order->payments
                        ->map(function ($payment) {
                            return [
                                'id' =>
                                    $payment->id,

                                'payment_method' =>
                                    $payment->payment_method,

                                'amount' =>
                                    (float) $payment->amount,

                                'status' =>
                                    $payment->status,

                                'payment_reference' =>
                                    $payment->payment_reference,

                                'transaction_reference' =>
                                    $payment->transaction_reference,

                                'created_at' =>
                                    $payment->created_at,

                                'paid_at' =>
                                    $payment->paid_at,
                            ];
                        })
                        ->values(),
                ],
            ]
        );
    }
}

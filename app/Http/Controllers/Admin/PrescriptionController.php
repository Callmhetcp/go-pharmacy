<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    /**
     * Display prescription submissions.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $prescriptions = Prescription::query()
            ->with([
                'user:id,name,email',
            ])
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('doctor_name', 'like', "%{$search}%")
                        ->orWhere('hospital_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Prescriptions/Index', [
            'prescriptions' => $prescriptions,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],

            'statuses' => [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'fulfilled',
            ],
        ]);
    }

    /**
     * Display a prescription.
     */
    public function show(Prescription $prescription): Response
    {
        $prescription->load([
            'user:id,name,email',
            'reviewer:id,name,email',
            'items.product',
        ]);

       $products = Product::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'sku',
            'price',
            'requires_prescription',
            'is_active',
        ]);

        return Inertia::render('Admin/Prescriptions/Show', [
            'prescription' => $prescription,
            'products' => $products,
        ]);
    }

    /**
     * Update prescription review status.
     */
    public function update(
        Request $request,
        Prescription $prescription
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,under_review,approved,rejected,fulfilled',
            ],

            'review_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'rejection_reason' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $status = $validated['status'];

        $updateData = [
            'status' => $status,
            'review_notes' => $validated['review_notes'] ?? null,
            'rejection_reason' => $status === 'rejected'
                ? ($validated['rejection_reason'] ?? null)
                : null,
        ];

        if (in_array($status, ['under_review', 'approved', 'rejected', 'fulfilled'])) {
            $updateData['reviewed_by'] = Auth::id();
            $updateData['reviewed_at'] = now();
        }

        if ($status === 'pending') {
            $updateData['reviewed_by'] = null;
            $updateData['reviewed_at'] = null;
        }

        $prescription->update($updateData);

        return redirect()
            ->route('admin.prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

    /**
     * Create an order from an approved prescription.
     */
    public function createOrder(
        Request $request,
        Prescription $prescription
    ): RedirectResponse {
        $prescription->load([
            'user',
            'items.product',
        ]);

        if (! $prescription->isApproved()) {
            return back()->with(
                'error',
                'Only approved prescriptions can be converted into an order.'
            );
        }

        if ($prescription->items->isEmpty()) {
            return back()->with(
                'error',
                'This prescription has no medicines added to it.'
            );
        }

        try {
            $order = DB::transaction(function () use ($prescription) {

                $subtotal = 0;

                foreach ($prescription->items as $item) {
                    if (! $item->product) {
                        throw new RuntimeException(
                            "The medicine \"{$item->medicine_name}\" is not linked to a product."
                        );
                    }

                    if (! $item->product->is_active) {
                        throw new RuntimeException(
                            "The medicine \"{$item->product->name}\" is no longer available."
                        );
                    }

                    $quantity = (int) $item->quantity;

                    if ($quantity < 1) {
                        throw new RuntimeException(
                            "Invalid quantity for {$item->medicine_name}."
                        );
                    }

                    $price = (float) $item->product->price;

                    $subtotal += $price * $quantity;
                }

                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),

                    'user_id' => $prescription->user_id,

                    'customer_name' => $prescription->user?->name ?? 'Customer',

                    'customer_email' => $prescription->user?->email ?? '',

                    'customer_phone' => '',

                    'delivery_address' => '',

                    'delivery_city' => null,

                    'delivery_state' => null,

                    'delivery_notes' => null,

                    'subtotal' => $subtotal,

                    'delivery_fee' => 0,

                    'discount' => 0,

                    'total' => $subtotal,

                    'status' => 'pending',

                    'payment_status' => 'unpaid',

                    'notes' => "Order created from prescription {$prescription->reference_number}.",
                ]);

                foreach ($prescription->items as $item) {
                    $product = $item->product;

                    $unitPrice = (float) $product->price;
                    $quantity = (int) $item->quantity;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'subtotal' => $unitPrice * $quantity,
                    ]);
                }

                return $order;
            });

            $prescription->update([
                'status' => 'fulfilled',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return redirect()
                ->route('admin.orders.show', $order)
                ->with(
                    'success',
                    "Order {$order->order_number} created successfully from the prescription."
                );

        } catch (RuntimeException $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'GP-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(6));

        } while (
            Order::where('order_number', $number)->exists()
        );

        return $number;
    }
}
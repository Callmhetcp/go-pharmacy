<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Display the authenticated customer's addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => $addresses,
            ],
        ]);
    }

    /**
     * Store a new customer address.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateAddress($request);

        $user = $request->user();

        $address = DB::transaction(function () use ($user, $validated) {
            $isDefault = $validated['is_default'] ?? false;

            /*
             * The first address is always the default address.
             */
            if (! $user->addresses()->exists()) {
                $isDefault = true;
            }

            /*
             * If this address is being made default,
             * remove the default status from all other addresses.
             */
            if ($isDefault) {
                $user->addresses()
                    ->where('is_default', true)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $validated['is_default'] = $isDefault;

            return $user->addresses()->create($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully.',
            'data' => [
                'address' => $address,
            ],
        ], 201);
    }

    /**
     * Display a single customer address.
     */
    public function show(
        Request $request,
        Address $address
    ): JsonResponse {
        $this->authorizeAddress($request, $address);

        return response()->json([
            'success' => true,
            'data' => [
                'address' => $address,
            ],
        ]);
    }

    /**
     * Update a customer address.
     */
    public function update(
        Request $request,
        Address $address
    ): JsonResponse {
        $this->authorizeAddress($request, $address);

        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($address, $validated) {
            /*
             * Preserve the current default state when is_default
             * was not included in the request.
             */
            $isDefault = array_key_exists(
                'is_default',
                $validated
            )
                ? (bool) $validated['is_default']
                : (bool) $address->is_default;

            /*
             * If this address is becoming the default,
             * remove the default status from other addresses.
             */
            if ($isDefault) {
                $address->user()
                    ->first()
                    ->addresses()
                    ->where('id', '!=', $address->id)
                    ->where('is_default', true)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            /*
             * Prevent an update from leaving the customer
             * without a default address when this address
             * was previously the default.
             */
            if (
                ! $isDefault
                && $address->is_default
            ) {
                $isDefault = true;
            }

            $validated['is_default'] = $isDefault;

            $address->update($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => [
                'address' => $address->fresh(),
            ],
        ]);
    }

    /**
     * Delete a customer address.
     */
    public function destroy(
        Request $request,
        Address $address
    ): JsonResponse {
        $this->authorizeAddress($request, $address);

        DB::transaction(function () use ($request, $address) {
            $wasDefault = (bool) $address->is_default;

            $address->delete();

            /*
             * If the deleted address was the default,
             * promote the newest remaining address.
             */
            if ($wasDefault) {
                $replacement = $request->user()
                    ->addresses()
                    ->latest()
                    ->first();

                if ($replacement) {
                    $replacement->update([
                        'is_default' => true,
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    /**
     * Set an address as the customer's default address.
     */
    public function setDefault(
        Request $request,
        Address $address
    ): JsonResponse {
        $this->authorizeAddress($request, $address);

        DB::transaction(function () use ($request, $address) {
            /*
             * Remove the default status from all
             * other addresses belonging to this customer.
             */
            $request->user()
                ->addresses()
                ->where('id', '!=', $address->id)
                ->where('is_default', true)
                ->update([
                    'is_default' => false,
                ]);

            $address->update([
                'is_default' => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully.',
            'data' => [
                'address' => $address->fresh(),
            ],
        ]);
    }

    /**
     * Validate address data.
     */
    protected function validateAddress(
        Request $request
    ): array {
        return $request->validate([
            'label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'recipient_name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'address' => [
                'required',
                'string',
                'max:1000',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'state' => [
                'required',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'delivery_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_default' => [
                'sometimes',
                'boolean',
            ],
        ]);
    }

    /**
     * Ensure the address belongs to the authenticated customer.
     */
    protected function authorizeAddress(
        Request $request,
        Address $address
    ): void {
        abort_unless(
            $address->user_id === $request->user()->id,
            404
        );
    }
}

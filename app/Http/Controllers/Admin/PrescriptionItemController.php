<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrescriptionItemController extends Controller
{
    /**
     * Store a prescription item.
     */
    public function store(
        Request $request,
        Prescription $prescription
    ): RedirectResponse {
        $validated = $request->validate([
            'product_id' => [
                'nullable',
                'exists:products,id',
            ],

            'medicine_name' => [
                'required',
                'string',
                'max:255',
            ],

            'dosage' => [
                'nullable',
                'string',
                'max:255',
            ],

            'frequency' => [
                'nullable',
                'string',
                'max:255',
            ],

            'duration' => [
                'nullable',
                'string',
                'max:255',
            ],

            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'instructions' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | If a Go Pharmacy product is selected, use its name when medicine
        | name was not explicitly supplied.
        |--------------------------------------------------------------------------
        */

        if (
            ! empty($validated['product_id']) &&
            empty($validated['medicine_name'])
        ) {
            $product = Product::find($validated['product_id']);

            $validated['medicine_name'] = $product?->name;
        }

        $prescription->items()->create($validated);

        return redirect()
            ->route('admin.prescriptions.show', $prescription)
            ->with('success', 'Prescription medicine added successfully.');
    }

    /**
     * Update a prescription item.
     */
    public function update(
        Request $request,
        Prescription $prescription,
        PrescriptionItem $item
    ): RedirectResponse {
        abort_unless(
            $item->prescription_id === $prescription->id,
            404
        );

        $validated = $request->validate([
            'product_id' => [
                'nullable',
                'exists:products,id',
            ],

            'medicine_name' => [
                'required',
                'string',
                'max:255',
            ],

            'dosage' => [
                'nullable',
                'string',
                'max:255',
            ],

            'frequency' => [
                'nullable',
                'string',
                'max:255',
            ],

            'duration' => [
                'nullable',
                'string',
                'max:255',
            ],

            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'instructions' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $item->update($validated);

        return redirect()
            ->route('admin.prescriptions.show', $prescription)
            ->with('success', 'Prescription medicine updated successfully.');
    }

    /**
     * Remove a prescription item.
     */
    public function destroy(
        Prescription $prescription,
        PrescriptionItem $item
    ): RedirectResponse {
        abort_unless(
            $item->prescription_id === $prescription->id,
            404
        );

        $item->delete();

        return redirect()
            ->route('admin.prescriptions.show', $prescription)
            ->with('success', 'Prescription medicine removed successfully.');
    }
}
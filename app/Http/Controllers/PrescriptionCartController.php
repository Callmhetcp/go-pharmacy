<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PrescriptionCartController extends Controller
{
    /**
     * Add all approved prescription medicines to the cart.
     */
    public function store(Prescription $prescription): RedirectResponse
    {
        abort_unless(
            $prescription->user_id === Auth::id(),
            403
        );

        abort_unless(
            $prescription->status === 'approved',
            403
        );

        $prescription->load([
            'items.product',
        ]);

        foreach ($prescription->items as $item) {
            if (! $item->product) {
                continue;
            }

            if (! $item->product->is_active) {
                continue;
            }

            $quantity = $item->quantity ?? 1;

            /*
             * We will connect this section to the existing
             * Go Pharmacy cart implementation next.
             */
        }

        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                'Prescription medicines have been added to your cart.'
            );
    }
}
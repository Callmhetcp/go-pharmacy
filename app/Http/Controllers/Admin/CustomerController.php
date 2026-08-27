<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display all customers.
     */
    public function index(Request $request): Response
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $customers = User::query()
            /*
            |--------------------------------------------------------------------------
            | Customers are normal users.
            |
            | Admin users are excluded using the existing
            | is_admin boolean. We do NOT use roles().
            |--------------------------------------------------------------------------
            */
            ->where('is_admin', false)

            /*
            |--------------------------------------------------------------------------
            | Customer statistics
            |--------------------------------------------------------------------------
            */
            ->withCount([
                'orders',
                'prescriptions',
            ])

            ->withSum(
                'orders',
                'total'
            )

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
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
                            );
                    });
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Newest customers first
            |--------------------------------------------------------------------------
            */
            ->latest()

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */
            ->paginate(15)

            ->withQueryString();

        return Inertia::render(
            'Admin/Customers/Index',
            [
                'customers' => $customers,

                'filters' => [
                    'search' => $search,
                ],
            ]
        );
    }

    /**
     * Display a single customer.
     */
    public function show(User $customer): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Do not allow an admin account to be viewed as a customer.
        |--------------------------------------------------------------------------
        */
        abort_if(
            $customer->is_admin,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Load customer history.
        |--------------------------------------------------------------------------
        */
        $customer->load([
            'orders' => function ($query) {
                $query
                    ->latest()
                    ->limit(10);
            },

            'prescriptions' => function ($query) {
                $query
                    ->latest()
                    ->limit(10);
            },
        ]);

        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */
        $customer->loadCount([
            'orders',
            'prescriptions',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Total amount actually paid.
        |--------------------------------------------------------------------------
        */
        $totalSpent = $customer
            ->orders()
            ->where(
                'payment_status',
                'paid'
            )
            ->sum('total');

        return Inertia::render(
            'Admin/Customers/Show',
            [
                'customer' => $customer,

                'totalSpent' => (float) $totalSpent,
            ]
        );
    }
}
<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a paginated list of customers.
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $customers = User::query()
            /*
            |--------------------------------------------------------------------------
            | Customers only
            |--------------------------------------------------------------------------
            |
            | Admin accounts are excluded using the existing is_admin field.
            |
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
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Display a single customer.
     */
    public function show(User $customer): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Do not expose admin accounts as customers.
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

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'total_spent' => (float) $totalSpent,
            ],
        ]);
    }
}

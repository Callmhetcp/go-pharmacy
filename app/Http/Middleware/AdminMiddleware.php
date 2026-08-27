<?php

namespace App\Http\Middleware;

use App\Models\PurchaseItem;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATION
        |--------------------------------------------------------------------------
        */

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden.',
                ], 403);
            }

            abort(403, 'Forbidden.');
        }

        /*
        |--------------------------------------------------------------------------
        | API REQUESTS
        |--------------------------------------------------------------------------
        |
        | Sanctum API requests are stateless and do not have a session store.
        |
        | The admin expiry reminder is an Inertia/session-based web feature,
        | so it must not run for API requests.
        |
        */

        if ($request->expectsJson()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN EXPIRY REMINDER
        |--------------------------------------------------------------------------
        |
        | This section is only used by the Inertia/web admin application.
        |
        | Show the reminder only once during the current admin login session.
        |
        */

        if (! $request->session()->has('admin_expiry_reminder_shown')) {
            $expiredProducts = PurchaseItem::query()
                ->with([
                    'product:id,name,sku',
                    'purchase:id,supplier_id',
                    'purchase.supplier:id,name,company_name',
                ])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', today())
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => (int) $item->id,
                        'product_id' => $item->product?->id,
                        'product_name' => $item->product?->name
                            ?? 'Unknown Product',
                        'sku' => $item->product?->sku,
                        'batch_number' => $item->batch_number,
                        'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                        'quantity' => (int) $item->quantity,
                        'supplier' => $item->purchase?->supplier?->company_name
                            ?: $item->purchase?->supplier?->name,
                    ];
                })
                ->values()
                ->all();

            Inertia::share('adminExpiryReminder', [
                'show' => count($expiredProducts) > 0,
                'products' => $expiredProducts,
            ]);

            $request->session()->put(
                'admin_expiry_reminder_shown',
                true
            );
        } else {
            Inertia::share('adminExpiryReminder', [
                'show' => false,
                'products' => [],
            ]);
        }

        return $next($request);
    }
}

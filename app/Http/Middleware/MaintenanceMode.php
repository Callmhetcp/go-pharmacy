<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function __construct(
        protected Settings $settings
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $maintenanceMode = (bool) $this->settings->get(
            'website.maintenance_mode',
            false
        );

        /*
        |--------------------------------------------------------------------------
        | Maintenance mode is OFF
        |--------------------------------------------------------------------------
        */

        if (! $maintenanceMode) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Allow Admin Panel
        |--------------------------------------------------------------------------
        |
        | Admin users must still be able to access the admin area
        | and turn maintenance mode off.
        |
        */

        if (
            $request->is('admin')
            || $request->is('admin/*')
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Show Maintenance Page
        |--------------------------------------------------------------------------
        */

        return response()->view('maintenance', [], 503);
    }
}
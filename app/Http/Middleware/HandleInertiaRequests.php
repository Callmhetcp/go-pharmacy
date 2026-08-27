<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $cart = $request->session()->get('cart', []);

        $cartCount = collect($cart)->sum(
            fn ($item) => (int) ($item['quantity'] ?? 0)
        );

        $cartSubtotal = collect($cart)->sum(
            fn ($item) => (float) ($item['subtotal'] ?? 0)
        );

        $settings = app(Settings::class);

        return [
            ...parent::share($request),

            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */
            'auth' => [
                'user' => $request->user(),
            ],

            /*
            |--------------------------------------------------------------------------
            | Cart
            |--------------------------------------------------------------------------
            */
            'cart' => [
                'count' => $cartCount,
                'subtotal' => $cartSubtotal,
            ],

            /*
            |--------------------------------------------------------------------------
            | Go Pharmacy Settings
            |--------------------------------------------------------------------------
            |
            | These settings are available globally to every Inertia page.
            | This allows the public website, checkout, navigation, footer,
            | receipts, etc. to respond to admin settings.
            |
            */
            'settings' => [
                'business' => [
                    'name' => $settings->get(
                        'business.name',
                        'Go Pharmacy'
                    ),
                    'tagline' => $settings->get(
                        'business.tagline',
                        'GOOD HEALTH. MADE SIMPLE.'
                    ),
                    'email' => $settings->get(
                        'business.email',
                        ''
                    ),
                    'phone' => $settings->get(
                        'business.phone',
                        ''
                    ),
                    'whatsapp' => $settings->get(
                        'business.whatsapp',
                        ''
                    ),
                    'address' => $settings->get(
                        'business.address',
                        ''
                    ),
                    'city' => $settings->get(
                        'business.city',
                        ''
                    ),
                    'state' => $settings->get(
                        'business.state',
                        ''
                    ),
                ],

                'website' => [
                    'title' => $settings->get(
                        'website.title',
                        'Go Pharmacy'
                    ),
                    'hero_title' => $settings->get(
                        'website.hero_title',
                        'Healthcare made simple.'
                    ),
                    'hero_subtitle' => $settings->get(
                        'website.hero_subtitle',
                        'Quality healthcare products delivered to your door.'
                    ),
                    'primary_color' => $settings->get(
                        'website.primary_color',
                        '#16A34A'
                    ),
                    'accent_color' => $settings->get(
                        'website.accent_color',
                        '#22C55E'
                    ),
                    'registration_enabled' => $settings->get(
                        'website.registration_enabled',
                        true
                    ),
                    'guest_checkout' => $settings->get(
                        'website.guest_checkout',
                        true
                    ),
                    'maintenance_mode' => $settings->get(
                        'website.maintenance_mode',
                        false
                    ),
                ],

                'orders' => [
                    'prefix' => $settings->get(
                        'orders.prefix',
                        'GP-'
                    ),
                    'minimum_amount' => $settings->get(
                        'orders.minimum_amount',
                        0
                    ),
                ],

                'delivery' => [
                    'enabled' => $settings->get(
                        'delivery.enabled',
                        true
                    ),
                    'standard_fee' => $settings->get(
                        'delivery.standard_fee',
                        0
                    ),
                    'free_threshold' => $settings->get(
                        'delivery.free_threshold',
                        0
                    ),
                    'pickup_enabled' => $settings->get(
                        'delivery.pickup_enabled',
                        true
                    ),
                    'pickup_address' => $settings->get(
                        'delivery.pickup_address',
                        ''
                    ),
                ],

                'payments' => [
                    'bank_transfer_enabled' => $settings->get(
                        'payments.bank_transfer_enabled',
                        true
                    ),
                    'cash_on_delivery_enabled' => $settings->get(
                        'payments.cash_on_delivery_enabled',
                        true
                    ),
                    'require_proof' => $settings->get(
                        'payments.require_proof',
                        true
                    ),
                    'bank_name' => $settings->get(
                        'payments.bank_name',
                        ''
                    ),
                    'account_name' => $settings->get(
                        'payments.account_name',
                        ''
                    ),
                    'account_number' => $settings->get(
                        'payments.account_number',
                        ''
                    ),
                    'instructions' => $settings->get(
                        'payments.instructions',
                        ''
                    ),
                ],

                'receipt' => [
                    'show_logo' => $settings->get(
                        'receipt.show_logo',
                        true
                    ),
                    'show_customer' => $settings->get(
                        'receipt.show_customer',
                        true
                    ),
                    'show_cashier' => $settings->get(
                        'receipt.show_cashier',
                        true
                    ),
                    'show_payment_method' => $settings->get(
                        'receipt.show_payment_method',
                        true
                    ),
                    'show_delivery_fee' => $settings->get(
                        'receipt.show_delivery_fee',
                        true
                    ),
                    'prefix' => $settings->get(
                        'receipt.prefix',
                        'GP-RCPT-'
                    ),
                    'footer' => $settings->get(
                        'receipt.footer',
                        ''
                    ),
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Flash Messages
            |--------------------------------------------------------------------------
            */
            'flash' => [
                'success' => fn () => $request->session()->get('success'),

                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
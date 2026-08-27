<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Settings $settings)
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'general' => $settings->all('general'),
                'website' => $settings->all('website'),
                'orders' => $settings->all('orders'),
                'payments' => $settings->all('payments'),
                'receipt' => $settings->all('receipt'),
            ],
        ]);
    }

    public function update(Request $request, Settings $settings)
    {
        $validated = $request->validate([
            /*
            | General
            */
            'business_name' => ['required', 'string', 'max:255'],
            'business_tagline' => ['nullable', 'string', 'max:255'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_whatsapp' => ['nullable', 'string', 'max:50'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_city' => ['nullable', 'string', 'max:100'],
            'business_state' => ['nullable', 'string', 'max:100'],

            /*
            | Website
            */
            'website_title' => ['required', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'registration_enabled' => ['boolean'],
            'guest_checkout' => ['boolean'],
            'maintenance_mode' => ['boolean'],

            /*
            | Orders & Delivery
            */
            'order_prefix' => ['required', 'string', 'max:20'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'delivery_enabled' => ['boolean'],
            'standard_delivery_fee' => ['required', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['required', 'numeric', 'min:0'],
            'pickup_enabled' => ['boolean'],
            'pickup_address' => ['nullable', 'string', 'max:500'],

            /*
            | Payments
            */
            'bank_transfer_enabled' => ['boolean'],
            'cash_on_delivery_enabled' => ['boolean'],
            'require_payment_proof' => ['boolean'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],

            /*
            | Receipt
            */
            'receipt_show_logo' => ['boolean'],
            'receipt_show_customer' => ['boolean'],
            'receipt_show_cashier' => ['boolean'],
            'receipt_show_payment_method' => ['boolean'],
            'receipt_show_delivery_fee' => ['boolean'],
            'receipt_prefix' => ['required', 'string', 'max:30'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'business.name',
            $validated['business_name'],
            'string',
            'general'
        );

        $settings->set(
            'business.tagline',
            $validated['business_tagline'] ?? '',
            'string',
            'general'
        );

        $settings->set(
            'business.email',
            $validated['business_email'] ?? '',
            'string',
            'general'
        );

        $settings->set(
            'business.phone',
            $validated['business_phone'] ?? '',
            'string',
            'general'
        );

        $settings->set(
            'business.whatsapp',
            $validated['business_whatsapp'] ?? '',
            'string',
            'general'
        );

        $settings->set(
            'business.address',
            $validated['business_address'] ?? '',
            'string',
            'general'
        );

        $settings->set(
            'business.city',
            $validated['business_city'] ?? '',
            'string',
            'general'
        );

        $settings->set(
            'business.state',
            $validated['business_state'] ?? '',
            'string',
            'general'
        );

        /*
        |--------------------------------------------------------------------------
        | Website
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'website.title',
            $validated['website_title'],
            'string',
            'website'
        );

        $settings->set(
            'website.hero_title',
            $validated['hero_title'] ?? '',
            'string',
            'website'
        );

        $settings->set(
            'website.hero_subtitle',
            $validated['hero_subtitle'] ?? '',
            'string',
            'website'
        );

        $settings->set(
            'website.primary_color',
            $validated['primary_color'],
            'string',
            'website'
        );

        $settings->set(
            'website.accent_color',
            $validated['accent_color'],
            'string',
            'website'
        );

        $settings->set(
            'website.registration_enabled',
            $validated['registration_enabled'] ?? false,
            'boolean',
            'website'
        );

        $settings->set(
            'website.guest_checkout',
            $validated['guest_checkout'] ?? false,
            'boolean',
            'website'
        );

        $settings->set(
            'website.maintenance_mode',
            $validated['maintenance_mode'] ?? false,
            'boolean',
            'website'
        );

        /*
        |--------------------------------------------------------------------------
        | Orders & Delivery
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'orders.prefix',
            $validated['order_prefix'],
            'string',
            'orders'
        );

        $settings->set(
            'orders.minimum_amount',
            $validated['minimum_order_amount'],
            'float',
            'orders'
        );

        $settings->set(
            'delivery.enabled',
            $validated['delivery_enabled'] ?? false,
            'boolean',
            'orders'
        );

        $settings->set(
            'delivery.standard_fee',
            $validated['standard_delivery_fee'],
            'float',
            'orders'
        );

        $settings->set(
            'delivery.free_threshold',
            $validated['free_delivery_threshold'],
            'float',
            'orders'
        );

        $settings->set(
            'delivery.pickup_enabled',
            $validated['pickup_enabled'] ?? false,
            'boolean',
            'orders'
        );

        $settings->set(
            'delivery.pickup_address',
            $validated['pickup_address'] ?? '',
            'string',
            'orders'
        );

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'payments.bank_transfer_enabled',
            $validated['bank_transfer_enabled'] ?? false,
            'boolean',
            'payments'
        );

        $settings->set(
            'payments.cash_on_delivery_enabled',
            $validated['cash_on_delivery_enabled'] ?? false,
            'boolean',
            'payments'
        );

        $settings->set(
            'payments.require_proof',
            $validated['require_payment_proof'] ?? false,
            'boolean',
            'payments'
        );

        $settings->set(
            'payments.bank_name',
            $validated['bank_name'] ?? '',
            'string',
            'payments'
        );

        $settings->set(
            'payments.account_name',
            $validated['account_name'] ?? '',
            'string',
            'payments'
        );

        $settings->set(
            'payments.account_number',
            $validated['account_number'] ?? '',
            'string',
            'payments'
        );

        $settings->set(
            'payments.instructions',
            $validated['payment_instructions'] ?? '',
            'string',
            'payments'
        );

        /*
        |--------------------------------------------------------------------------
        | Receipt
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'receipt.show_logo',
            $validated['receipt_show_logo'] ?? false,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_customer',
            $validated['receipt_show_customer'] ?? false,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_cashier',
            $validated['receipt_show_cashier'] ?? false,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_payment_method',
            $validated['receipt_show_payment_method'] ?? false,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_delivery_fee',
            $validated['receipt_show_delivery_fee'] ?? false,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.prefix',
            $validated['receipt_prefix'],
            'string',
            'receipt'
        );

        $settings->set(
            'receipt.footer',
            $validated['receipt_footer'] ?? '',
            'string',
            'receipt'
        );

        return back()->with(
            'success',
            'Settings updated successfully.'
        );
    }
}
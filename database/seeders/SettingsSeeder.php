<?php

namespace Database\Seeders;

use App\Support\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(Settings::class);

        /*
        |--------------------------------------------------------------------------
        | General
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'business.name',
            'Go Pharmacy',
            'string',
            'general'
        );

        $settings->set(
            'business.tagline',
            'GOOD HEALTH. MADE SIMPLE.',
            'string',
            'general'
        );

        $settings->set(
            'business.email',
            '',
            'string',
            'general'
        );

        $settings->set(
            'business.phone',
            '',
            'string',
            'general'
        );

        $settings->set(
            'business.whatsapp',
            '',
            'string',
            'general'
        );

        $settings->set(
            'business.address',
            '',
            'string',
            'general'
        );

        $settings->set(
            'business.city',
            '',
            'string',
            'general'
        );

        $settings->set(
            'business.state',
            '',
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
            'Go Pharmacy',
            'string',
            'website'
        );

        $settings->set(
            'website.hero_title',
            'Healthcare made simple.',
            'string',
            'website'
        );

        $settings->set(
            'website.hero_subtitle',
            'Quality healthcare products delivered to your door.',
            'string',
            'website'
        );

        $settings->set(
            'website.primary_color',
            '#16A34A',
            'string',
            'website'
        );

        $settings->set(
            'website.accent_color',
            '#22C55E',
            'string',
            'website'
        );

        $settings->set(
            'website.registration_enabled',
            true,
            'boolean',
            'website'
        );

        $settings->set(
            'website.guest_checkout',
            true,
            'boolean',
            'website'
        );

        $settings->set(
            'website.maintenance_mode',
            false,
            'boolean',
            'website'
        );

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'orders.prefix',
            'GP-',
            'string',
            'orders'
        );

        $settings->set(
            'orders.minimum_amount',
            0,
            'float',
            'orders'
        );

        /*
        |--------------------------------------------------------------------------
        | Delivery
        |--------------------------------------------------------------------------
        */

        $settings->set(
            'delivery.enabled',
            true,
            'boolean',
            'orders'
        );

        $settings->set(
            'delivery.standard_fee',
            0,
            'float',
            'orders'
        );

        $settings->set(
            'delivery.free_threshold',
            0,
            'float',
            'orders'
        );

        $settings->set(
            'delivery.pickup_enabled',
            true,
            'boolean',
            'orders'
        );

        $settings->set(
            'delivery.pickup_address',
            '',
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
            true,
            'boolean',
            'payments'
        );

        $settings->set(
            'payments.cash_on_delivery_enabled',
            true,
            'boolean',
            'payments'
        );

        $settings->set(
            'payments.require_proof',
            true,
            'boolean',
            'payments'
        );

        $settings->set(
            'payments.bank_name',
            '',
            'string',
            'payments'
        );

        $settings->set(
            'payments.account_name',
            '',
            'string',
            'payments'
        );

        $settings->set(
            'payments.account_number',
            '',
            'string',
            'payments'
        );

        $settings->set(
            'payments.instructions',
            '',
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
            true,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_customer',
            true,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_cashier',
            true,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_payment_method',
            true,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.show_delivery_fee',
            true,
            'boolean',
            'receipt'
        );

        $settings->set(
            'receipt.prefix',
            'GP-RCPT-',
            'string',
            'receipt'
        );

        $settings->set(
            'receipt.footer',
            'Thank you for choosing Go Pharmacy.',
            'string',
            'receipt'
        );
    }
}
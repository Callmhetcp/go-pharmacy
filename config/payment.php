<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */

    'default_gateway' => env(
        'PAYMENT_GATEWAY',
        'flutterwave'
    ),

    /*
    |--------------------------------------------------------------------------
    | Flutterwave
    |--------------------------------------------------------------------------
    */

    'flutterwave' => [

        'public_key' => env(
            'FLW_PUBLIC_KEY'
        ),

        'secret_key' => env(
            'FLW_SECRET_KEY'
        ),

        'encryption_key' => env(
            'FLW_ENCRYPTION_KEY'
        ),

        'base_url' => env(
            'FLW_BASE_URL',
            'https://api.flutterwave.com/v3'
        ),

    ],

];
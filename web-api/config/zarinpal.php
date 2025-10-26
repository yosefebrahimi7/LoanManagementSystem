<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Zarinpal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Zarinpal payment gateway integration
    |
    */

    'merchant_id' => env('ZARINPAL_MERCHANT_ID', '00000000-0000-0000-0000-000000000000'),

    'sandbox' => env('ZARINPAL_SANDBOX', true),

    'callback_url' => env('ZARINPAL_CALLBACK_URL', 'http://localhost:8000/api/payment/callback'),

];


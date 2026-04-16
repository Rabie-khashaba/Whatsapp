<?php

return [
    // Paymob Accept base url. You can pass with or without trailing `/api`.
    // Examples:
    // - https://accept.paymob.com
    // - https://accept.paymob.com/api
    'base_url' => env('PAYMOB_BASE_URL', env('BAYMOB_BASE_URL', 'https://accept.paymob.com/api')),

    // API key from Paymob dashboard
    'api_key' => env('PAYMOB_API_KEY', env('BAYMOB_API_KEY')),

    // Flash Checkout public key.
    'public_key' => env('PAYMOB_PUBLIC_KEY', env('BAYMOB_PUBLIC_KEY')),

    // Flash Checkout secret key.
    'secret_key' => env('PAYMOB_SECRET_KEY', env('BAYMOB_SECRET_KEY')),

    // Default currency
    'currency' => env('PAYMOB_CURRENCY', 'EGP'),

    // Single integration id or comma separated list: "5481966,12345"
    // Backward compatible env: BAYMOB_INTEGRATION_ID
    'integration_id' => env('PAYMOB_INTEGRATION_ID', env('BAYMOB_INTEGRATION_ID')),

    // Hosted card iframe id from Paymob dashboard.
    'iframe_id' => env('PAYMOB_IFRAME_ID', env('BAYMOB_IFRAME_ID')),
];

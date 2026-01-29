<?php

return [
    'gateways' => [
        'credit_card' => [
            'api_key' => env('CREDIT_CARD_API_KEY'),
            'api_secret' => env('CREDIT_CARD_API_SECRET'),
        ],
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
        ],
    ],
];

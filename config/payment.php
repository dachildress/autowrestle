<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment provider (future: Square)
    |--------------------------------------------------------------------------
    | When payment for tournament creation is confirmed (e.g. via Square webhook),
    | the tournament can be automatically approved (pending_approval = false).
    | Square integration: receive payment online to create a tournament;
    | once payment is confirmed, call Tournament::find($id)->approve() or
    | equivalent to make the tournament active and visible.
    */
    'provider' => env('PAYMENT_PROVIDER', null), // 'square' when implemented
    'square' => [
        'location_id' => env('SQUARE_LOCATION_ID'),
        'access_token' => env('SQUARE_ACCESS_TOKEN'),
        'environment' => env('SQUARE_ENVIRONMENT', 'sandbox'),
    ],
];

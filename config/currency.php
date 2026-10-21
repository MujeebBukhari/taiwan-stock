<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default and Secondary System Currencies
    |--------------------------------------------------------------------------
    | Primary accounting currency is TWD (Taiwan Dollar).
    | USD is available as secondary selectable display currency.
    */
    'default' => env('DEFAULT_CURRENCY', 'TWD'),
    'secondary' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Supported Display Currencies
    |--------------------------------------------------------------------------
    */
    'currencies' => [
        'TWD' => [
            'code' => 'TWD',
            'name' => 'New Taiwan Dollar',
            'symbol' => 'NT$',
            'decimals' => 0,
        ],
        'USD' => [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => 'US$',
            'decimals' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate Service Configuration
    |--------------------------------------------------------------------------
    | Documented, configurable exchange-rate source with cache and fallback.
    | 1 USD = 32.0 TWD -> 1 TWD = 0.03125 USD
    */
    'rates' => [
        'source' => env('CURRENCY_RATE_SOURCE', 'fixed'),
        'cache_ttl' => (int) env('CURRENCY_RATE_CACHE_TTL', 3600),
        'fixed' => [
            'TWD_USD' => env('EXCHANGE_RATE_TWD_USD', '0.03125000'),
            'USD_TWD' => env('EXCHANGE_RATE_USD_TWD', '32.00000000'),
        ],
    ],
];

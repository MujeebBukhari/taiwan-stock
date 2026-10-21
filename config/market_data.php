<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Market Data Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "taiwan" (TWSE, TPEx)
    |
    */
    'default' => env('MARKET_DATA_PROVIDER', 'taiwan'),

    /*
    |--------------------------------------------------------------------------
    | Quote Cache Duration (in seconds)
    |--------------------------------------------------------------------------
    |
    | Duration to cache quotes to prevent repetitive external API calls.
    |
    */
    'cache_ttl' => (int) env('MARKET_DATA_CACHE_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Fallback Cache Duration (in seconds)
    |--------------------------------------------------------------------------
    |
    | Extended duration to retain last-known quote for graceful degradation
    | during provider outages.
    |
    */
    'fallback_cache_ttl' => (int) env('MARKET_DATA_FALLBACK_CACHE_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Market Data Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'taiwan' => [
            'name' => 'Taiwan Stock Exchange (TWSE / TPEx)',
            'base_url' => env('TAIWAN_MARKET_DATA_BASE_URL', 'https://mis.twse.com.tw'),
            'api_key' => env('TAIWAN_MARKET_DATA_API_KEY', null),
            'timeout' => (int) env('TAIWAN_MARKET_DATA_TIMEOUT', 5),
            'supported_exchanges' => ['TWSE', 'TPEx'],
        ],
    ],
];

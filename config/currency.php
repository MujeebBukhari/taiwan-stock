<?php

return [
    'default' => env('DEFAULT_CURRENCY', 'TWD'),
    'secondary' => 'USD',
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
];

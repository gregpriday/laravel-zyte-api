<?php

return [
    'api' => [
        'key' => env('ZYTE_API_KEY'),
        'concurrency' => env('ZYTE_API_CONCURRENCY', 5),
    ],
    'proxy' => env('ZYTE_PROXY'),
];

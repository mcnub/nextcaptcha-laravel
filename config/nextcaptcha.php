<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NextCaptcha API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the NextCaptcha API
    | integration. You can set your API keys, logging preferences, and other
    | settings here.
    |
    */

    'client_key' => env('NEXTCAPTCHA_CLIENT_KEY', ''),

    'soft_id' => env('NEXTCAPTCHA_SOFT_ID', ''),

    'callback_url' => env('NEXTCAPTCHA_CALLBACK_URL', ''),

    'open_log' => env('NEXTCAPTCHA_OPEN_LOG', true),

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Configure how long to wait for captcha solutions (in seconds)
    |
    */
    'timeout' => env('NEXTCAPTCHA_TIMEOUT', 45),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    |
    | Configure the Guzzle HTTP client used for API requests
    |
    */
    'http_client' => [
        'verify' => false,
        'timeout' => 60,
        'pool_maxsize' => 1000,
    ],
];

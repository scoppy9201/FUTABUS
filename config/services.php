<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sepay' => [
        'refund_mode'        => env('SEPAY_REFUND_MODE', 'manual'),
        'environment'        => env('SEPAY_ENVIRONMENT', 'live'),
        'refund_enabled'     => env('SEPAY_REFUND_ENABLED', false),
        'refund_environment' => env('SEPAY_REFUND_ENVIRONMENT', 'sandbox'),
        'refund_token'       => env('SEPAY_REFUND_TOKEN'),
        'refund_bank_id'     => env('SEPAY_REFUND_BANK_ID'),
        'enabled'            => env('SEPAY_ENABLED', false),
        'bank'               => env('SEPAY_BANK'),
        'account_no'         => env('SEPAY_ACCOUNT_NO'),
        'webhook_auth'       => env('SEPAY_WEBHOOK_AUTH', 'hmac'),
        'webhook_secret'     => env('SEPAY_WEBHOOK_SECRET'),
        'webhook_key'        => env('SEPAY_WEBHOOK_KEY'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),
    ],

    'gemini' => [
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'keys'  => array_values(array_filter([
            env('GEMINI_API_KEY_1'),
            env('GEMINI_API_KEY_2'),
            env('GEMINI_API_KEY_3'),
        ])),
        'timeout' => env('GEMINI_TIMEOUT', 30),
    ],
];

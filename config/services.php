<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
    ],

    'monnify' => [
        'api_key'       => env('MONNIFY_API_KEY'),
        'secret_key'    => env('MONNIFY_SECRET_KEY'),
        'contract_code' => env('MONNIFY_CONTRACT_CODE'),
        'base_url'      => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),
    ],

    // ── JuicyWay ─────────────────────────────────────────────────────────────
    // Used for payment link creation and inbound webhook checksum verification.
    // Credentials are provided by PayGrid (they manage the JuicyWay account).
    // NEVER commit these values to git.
    'juicyway' => [
        'api_key'     => env('JUICYWAY_API_KEY'),
        'business_id' => env('JUICYWAY_BUSINESS_ID'),   // used for checksum verification ONLY
        'base_url'    => env('JUICYWAY_API_BASE_URL', 'https://api.spendjuice.com'),
    ],

];

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
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paxos' => [
        'base_url' => env('PAXOS_BASE_URL', 'https://api.sandbox.paxos.com'),
        'api_token' => env('PAXOS_API_TOKEN'),
        'client_id' => env('PAXOS_CLIENT_ID'),
        'client_secret' => env('PAXOS_CLIENT_SECRET'),
        'scope' => env('PAXOS_SCOPE', 'identity:write_identity identity:read_identity identity:write_account identity:read_account funding:read_profile'),
        // When true, omit identity_id/account_id on deposit-address (and similar) calls — required for first-party; third-party sets false.
        'first_party' => env('PAXOS_FIRST_PARTY', true),
        // Inbound webhooks: Paxos sends this header with the value you configure in Dashboard (API Key auth).
        'webhook_header' => env('PAXOS_WEBHOOK_HEADER', 'X-Paxos-Webhook-Key'),
        'webhook_secret' => env('PAXOS_WEBHOOK_SECRET'),
    ],

];

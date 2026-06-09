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

    'google_sheets' => [
        'webhook_url' => env(
            'GOOGLE_SHEETS_WEBHOOK_URL',
            env('OPENCLAW_GOOGLE_SHEETS_WEBHOOK_URL', env('LEADS_GOOGLE_SHEETS_WEBHOOK_URL'))
        ),
        'webhook_secret' => env('GOOGLE_SHEETS_WEBHOOK_SECRET', 'CHANGE_THIS_SECRET'),
    ],

    'leads' => [
        'webhook_token' => env('LEAD_WEBHOOK_TOKEN'),
    ],

    'calendly' => [
        'webhook_token' => env('CALENDLY_WEBHOOK_TOKEN'),
        'url' => env('CALENDLY_URL', 'https://calendly.com/salehbasahel/saleh-basahel'),
    ],

    'openclaw' => [
        'messaging_enabled' => env('OPENCLAW_MESSAGING_ENABLED', false),
        'binary' => env('OPENCLAW_BINARY', '/home/openclaw/.local/bin/openclaw'),
        'sender' => env('OPENCLAW_SENDER', '/usr/local/bin/saleh-openclaw-send'),
        'user' => env('OPENCLAW_USER', 'openclaw'),
        'home' => env('OPENCLAW_HOME', '/home/openclaw'),
        'path' => env('OPENCLAW_PATH', '/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin'),
        'admin_whatsapp' => env('ADMIN_WHATSAPP_NUMBER', '+971555574958'),
    ],

];

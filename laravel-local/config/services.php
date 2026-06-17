<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Store only active production integrations here. AI provider credentials are
    | intentionally modular so OpenRouter can be enabled later without changing
    | the lead collection or WhatsApp workflow code.
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

    'ai' => [
        'provider' => env('AI_PROVIDER', 'openrouter'),
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'model' => env('OPENROUTER_MODEL', 'openrouter/auto'),
            'timeout' => (int) env('OPENROUTER_TIMEOUT', 30),
            'max_retries' => (int) env('OPENROUTER_MAX_RETRIES', 2),
        ],
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
        'timeout' => (int) env('OPENCLAW_SEND_TIMEOUT', 45),
        'max_attempts' => (int) env('OPENCLAW_SEND_MAX_ATTEMPTS', 2),
    ],

];

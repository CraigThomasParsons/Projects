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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'piper' => [
        'token' => env('PIPER_TOKEN'),
        'browser_sync_url' => env('PIPER_BROWSER_SYNC_URL'),
    ],

    'anthropic' => [
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-opus-4-6'),
    ],

    'project_projection' => [
        'timeout_seconds' => env('PROJECT_PROJECTION_TIMEOUT_SECONDS', 8),
        'targets' => [
            [
                'name' => 'writersroom',
                'url' => env('WRITERSROOM_PROJECT_SYNC_URL'),
                'token' => env('WRITERSROOM_PROJECT_SYNC_TOKEN'),
            ],
            [
                'name' => 'devbacklog',
                'url' => env('DEVBACKLOG_PROJECT_SYNC_URL'),
                'token' => env('DEVBACKLOG_PROJECT_SYNC_TOKEN'),
                // base_url is the scheme+host, used for webhook calls beyond the projection-sync path.
                'base_url' => env('DEVBACKLOG_BASE_URL', 'http://dev.elasticgun.com'),
            ],
        ],
    ],

];

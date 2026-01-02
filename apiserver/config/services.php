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

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Supported providers: "log", "fast2sms"
    | Use "log" for development/testing (SMS will be logged instead of sent)
    | Set SMS_PROVIDER=fast2sms in production with proper API keys
    |
    | Database-backed providers (sms_providers table) take precedence over config.
    | Config is fallback when no database providers exist.
    |
    */
    'sms' => [
        // Default provider (fallback when no DB providers)
        'provider' => env('SMS_PROVIDER', 'log'),

        // Fast2SMS Configuration
        'fast2sms' => [
            'api_key' => env('FAST2SMS_API_KEY', env('FAST2SMS')),
            'sender_id' => env('FAST2SMS_SENDER_ID'),
            'entity_id' => env('FAST2SMS_ENTITY_ID'),
            'per_sms_cost' => env('FAST2SMS_PER_SMS_COST', 0.25),
            'min_balance_threshold' => env('FAST2SMS_MIN_BALANCE', 10.0),
        ],

        // SMS Sending Options
        'options' => [
            // Queue SMS sending via jobs (recommended for production)
            'queue' => env('SMS_QUEUE_ENABLED', true),
            'queue_name' => env('SMS_QUEUE_NAME', 'notifications'),

            // Retry failed SMS
            'max_retries' => env('SMS_MAX_RETRIES', 3),
            'retry_delay' => env('SMS_RETRY_DELAY', 60), // seconds

            // Demo mode (use static OTP 123456)
            'demo_mode' => env('SMS_DEMO_MODE', false),
            'demo_otp' => env('SMS_DEMO_OTP', '123456'),
        ],

        // Balance Alerts
        'alerts' => [
            'low_balance_threshold' => env('SMS_LOW_BALANCE_ALERT', 100),
            'notify_emails' => env('SMS_ALERT_EMAILS', ''), // comma-separated
        ],
    ],

    'payment' => [
      'cashfree' => [
          'key' => env('CASH_FREE_PAYMENT_KEY'),
          'secret' => env('CASH_FREE_PAYMENT_SECRET'),
          'sandbox' => env('APP_ENV') == 'local',
          'webhook' => env('CASH_FREE_PAYMENT_WEBHOOK'),
      ]
    ],

    'payout' => [
        'cashfree' => [
            'key' => env('CASH_FREE_PAYOUT_KEY'),
            'secret' => env('CASH_FREE_PAYOUT_SECRET'),
            'sandbox' => env('APP_ENV') == 'local',
            'webhook' => env('CASH_FREE_PAYOUT_WEBHOOK'),
        ]
    ],



];

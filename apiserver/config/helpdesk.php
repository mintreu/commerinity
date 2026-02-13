<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Helpdesk General Settings
    |--------------------------------------------------------------------------
    |
    | General configuration for the helpdesk system including ticket
    | settings, auto-close policies, and notification preferences.
    |
    */

    'enabled' => env('HELPDESK_ENABLED', true),

    'ticket' => [
        // UUID prefix for tickets (e.g., TICKET-2412-XXXXXXXX)
        'prefix' => env('HELPDESK_TICKET_PREFIX', 'TICKET'),

        // Default priority for new tickets
        'default_priority' => env('HELPDESK_DEFAULT_PRIORITY', 'medium'),

        // Auto-close tickets after X days of inactivity (0 = disabled)
        'auto_close_days' => env('HELPDESK_AUTO_CLOSE_DAYS', 7),

        // Max attachments per ticket
        'max_attachments' => env('HELPDESK_MAX_ATTACHMENTS', 5),

        // Max attachment size in KB
        'max_attachment_size' => env('HELPDESK_MAX_ATTACHMENT_SIZE', 10240), // 10MB

        // Allowed attachment mime types
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/zip',
            'text/plain',
        ],
    ],

    'conversation' => [
        // Max message length
        'max_message_length' => env('HELPDESK_MAX_MESSAGE_LENGTH', 5000),

        // Max attachments per message
        'max_attachments_per_message' => env('HELPDESK_MAX_ATTACHMENTS_PER_MESSAGE', 3),
    ],

    'faq' => [
        // Track FAQ views
        'track_views' => env('HELPDESK_FAQ_TRACK_VIEWS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure how and when notifications are sent for helpdesk events.
    |
    */

    'notifications' => [
        // Notify user on ticket status change
        'on_status_change' => env('HELPDESK_NOTIFY_STATUS_CHANGE', true),

        // Notify user on new reply from admin
        'on_admin_reply' => env('HELPDESK_NOTIFY_ADMIN_REPLY', true),

        // Notify admins on new ticket
        'on_new_ticket' => env('HELPDESK_NOTIFY_NEW_TICKET', true),

        // Email channel
        'email_enabled' => env('HELPDESK_EMAIL_NOTIFICATIONS', true),

        // Push notification channel
        'push_enabled' => env('HELPDESK_PUSH_NOTIFICATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Chatbot Integration (Future Plugin)
    |--------------------------------------------------------------------------
    |
    | Configuration for AI chatbot integration. When enabled, the chatbot
    | will handle initial user queries and can escalate to human support
    | by creating helpdesk tickets.
    |
    | Supported providers: 'mintreu', 'openai', 'huggingface', 'custom'
    |
    */

    'chatbot' => [
        // Master switch for chatbot functionality
        'enabled' => env('HELPDESK_CHATBOT_ENABLED', false),

        // Provider configuration
        'provider' => [
            // Provider name: 'mintreu', 'openai', 'huggingface', 'custom', null
            'name' => env('HELPDESK_CHATBOT_PROVIDER', null),

            // API endpoint (for custom providers or self-hosted)
            'api' => env('HELPDESK_CHATBOT_API_URL', null),

            // API key for authentication
            'key' => env('HELPDESK_CHATBOT_API_KEY', null),

            // API secret (if required by provider)
            'secret' => env('HELPDESK_CHATBOT_API_SECRET', null),

            // Model/Space identifier (e.g., 'gpt-4', 'mistral-7b', space ID)
            'model' => env('HELPDESK_CHATBOT_MODEL', null),
        ],

        // Chatbot behavior settings
        'behavior' => [
            // Check FAQs first before AI inference
            'check_faq_first' => env('HELPDESK_CHATBOT_CHECK_FAQ', true),

            // FAQ similarity threshold (0-1) to consider a match
            'faq_match_threshold' => env('HELPDESK_CHATBOT_FAQ_THRESHOLD', 0.7),

            // Allow user to request human support
            'allow_escalation' => env('HELPDESK_CHATBOT_ALLOW_ESCALATION', true),

            // Auto-escalate after X failed responses
            'auto_escalate_after' => env('HELPDESK_CHATBOT_AUTO_ESCALATE', 3),

            // Greeting message
            'greeting' => env('HELPDESK_CHATBOT_GREETING', 'Hello! How can I help you today?'),

            // Fallback message when bot can\'t help
            'fallback_message' => env('HELPDESK_CHATBOT_FALLBACK', 'I\'m not sure about that. Would you like to speak with a human agent?'),
        ],

        // Widget appearance (for embedded widget)
        'widget' => [
            'position' => env('HELPDESK_CHATBOT_POSITION', 'bottom-right'), // bottom-right, bottom-left
            'primary_color' => env('HELPDESK_CHATBOT_COLOR', '#6366f1'),
            'show_branding' => env('HELPDESK_CHATBOT_BRANDING', true),
            'auto_open_delay' => env('HELPDESK_CHATBOT_AUTO_OPEN', 0), // seconds, 0 = disabled
        ],

        // Logging & Analytics
        'logging' => [
            // Log all conversations
            'enabled' => env('HELPDESK_CHATBOT_LOGGING', true),

            // Store in database (for analytics)
            'store_conversations' => env('HELPDESK_CHATBOT_STORE_CONVERSATIONS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Protect the helpdesk system from abuse with rate limiting.
    |
    */

    'rate_limits' => [
        // Max tickets per user per day
        'tickets_per_day' => env('HELPDESK_MAX_TICKETS_PER_DAY', 10),

        // Max messages per ticket per hour
        'messages_per_hour' => env('HELPDESK_MAX_MESSAGES_PER_HOUR', 30),

        // Max chatbot queries per session
        'chatbot_queries_per_session' => env('HELPDESK_MAX_CHATBOT_QUERIES', 50),
    ],

];

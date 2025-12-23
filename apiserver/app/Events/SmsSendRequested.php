<?php

declare(strict_types=1);

namespace App\Events;

use App\Services\Sms\DTOs\SmsRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when SMS needs to be sent.
 *
 * Listeners can queue the SMS for async processing.
 */
class SmsSendRequested
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly SmsRequest $request,
        public readonly bool $immediate = false,
    ) {}

    /**
     * Create OTP send event.
     */
    public static function otp(string $phone, string $otp, ?int $userId = null): self
    {
        return new self(
            request: SmsRequest::otp($phone, $otp, $userId ? \App\Models\User::find($userId) : null),
            immediate: true, // OTPs should be sent immediately
        );
    }

    /**
     * Create transactional send event.
     */
    public static function transactional(string $phone, string $message, ?int $userId = null): self
    {
        return new self(
            request: SmsRequest::single(
                recipient: $phone,
                message: $message,
                type: 'transactional',
                user: $userId ? \App\Models\User::find($userId) : null,
            ),
        );
    }

    /**
     * Create promotional bulk send event.
     *
     * @param  array<string>  $phones
     */
    public static function promotional(array $phones, string $message, ?string $templateSlug = null): self
    {
        return new self(
            request: SmsRequest::bulk(
                recipients: $phones,
                message: $message,
                type: 'promotional',
                templateSlug: $templateSlug,
            ),
        );
    }
}

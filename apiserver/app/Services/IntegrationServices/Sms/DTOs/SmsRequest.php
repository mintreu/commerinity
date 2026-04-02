<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\DTOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * SMS Request DTO - Encapsulates all data needed to send an SMS.
 */
final readonly class SmsRequest
{
    /**
     * @param  array<string>  $recipients  Phone numbers
     * @param  string  $message  Message content OR template variables pipe-separated
     * @param  string  $type  Message type: otp, transactional, promotional, alert
     * @param  string|null  $templateSlug  Template slug for DLT SMS
     * @param  array<string, mixed>|null  $variables  Template variables
     * @param  User|null  $user  Associated user
     * @param  Model|null  $loggable  Related model (Order, Transaction, etc.)
     * @param  string  $source  Source: web, api, job, console
     * @param  array<string, mixed>  $metadata  Additional context data
     */
    public function __construct(
        public array $recipients,
        public string $message,
        public string $type = 'transactional',
        public ?string $templateSlug = null,
        public ?array $variables = null,
        public ?User $user = null,
        public ?Model $loggable = null,
        public string $source = 'api',
        public array $metadata = [],
    ) {}

    /**
     * Create from a single recipient.
     */
    public static function single(
        string $recipient,
        string $message,
        string $type = 'transactional',
        ?string $templateSlug = null,
        ?array $variables = null,
        ?User $user = null,
    ): self {
        return new self(
            recipients: [$recipient],
            message: $message,
            type: $type,
            templateSlug: $templateSlug,
            variables: $variables,
            user: $user,
        );
    }

    /**
     * Create OTP request.
     */
    public static function otp(string $recipient, string $otp, ?User $user = null): self
    {
        return new self(
            recipients: [$recipient],
            message: $otp, // Will be formatted by template
            type: 'otp',
            templateSlug: 'otp-verification',
            variables: [
                'otp' => $otp,
                'purpose' => 'verification',
                'validity' => '10',
                'app_name' => (string) config('app.name', 'Our App'),
            ],
            user: $user,
        );
    }

    /**
     * Create bulk SMS request.
     *
     * @param  array<string>  $recipients
     */
    public static function bulk(
        array $recipients,
        string $message,
        string $type = 'promotional',
        ?string $templateSlug = null,
        ?array $variables = null,
    ): self {
        return new self(
            recipients: $recipients,
            message: $message,
            type: $type,
            templateSlug: $templateSlug,
            variables: $variables,
        );
    }

    /**
     * Get first recipient (for single SMS).
     */
    public function getRecipient(): string
    {
        return $this->recipients[0] ?? '';
    }

    /**
     * Get recipient count.
     */
    public function getRecipientCount(): int
    {
        return count($this->recipients);
    }

    /**
     * Check if bulk request.
     */
    public function isBulk(): bool
    {
        return count($this->recipients) > 1;
    }

    /**
     * Check if using template.
     */
    public function usesTemplate(): bool
    {
        return $this->templateSlug !== null;
    }

    /**
     * Convert variables to pipe-separated string for Fast2SMS.
     */
    public function getVariablesAsPipeString(): string
    {
        if (empty($this->variables)) {
            return '';
        }

        return implode('|', array_values($this->variables));
    }
}

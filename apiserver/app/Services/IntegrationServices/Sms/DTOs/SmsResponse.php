<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\DTOs;

/**
 * SMS Response DTO - Standardized response from SMS providers.
 */
final readonly class SmsResponse
{
    /**
     * @param  bool  $success  Whether SMS was sent successfully
     * @param  string  $message  Human-readable status message
     * @param  string|null  $requestId  Provider request ID for tracking
     * @param  string|null  $messageId  Provider message ID
     * @param  string|null  $status  Delivery status: pending, queued, sent, delivered, failed
     * @param  string|null  $errorCode  Provider error code
     * @param  string|null  $errorMessage  Provider error message
     * @param  float  $cost  Cost of this SMS
     * @param  int  $segments  Number of SMS segments
     * @param  array<string, mixed>  $providerData  Raw provider response
     */
    public function __construct(
        public bool $success,
        public string $message,
        public ?string $requestId = null,
        public ?string $messageId = null,
        public string $status = 'pending',
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public float $cost = 0.0,
        public int $segments = 1,
        public array $providerData = [],
    ) {}

    /**
     * Create success response.
     */
    public static function success(
        string $message = 'SMS sent successfully',
        ?string $requestId = null,
        ?string $messageId = null,
        float $cost = 0.0,
        array $providerData = [],
    ): self {
        return new self(
            success: true,
            message: $message,
            requestId: $requestId,
            messageId: $messageId,
            status: 'sent',
            cost: $cost,
            providerData: $providerData,
        );
    }

    /**
     * Create failure response.
     */
    public static function failure(
        string $message,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        array $providerData = [],
    ): self {
        return new self(
            success: false,
            message: $message,
            status: 'failed',
            errorCode: $errorCode,
            errorMessage: $errorMessage ?? $message,
            providerData: $providerData,
        );
    }

    /**
     * Create insufficient balance response.
     */
    public static function insufficientBalance(float $currentBalance, float $required): self
    {
        return new self(
            success: false,
            message: "Insufficient balance. Current: {$currentBalance}, Required: {$required}",
            status: 'failed',
            errorCode: 'INSUFFICIENT_BALANCE',
            errorMessage: 'Provider wallet balance too low to send SMS',
        );
    }

    /**
     * Create provider unavailable response.
     */
    public static function providerUnavailable(string $provider, string $reason): self
    {
        return new self(
            success: false,
            message: "Provider {$provider} unavailable: {$reason}",
            status: 'failed',
            errorCode: 'PROVIDER_UNAVAILABLE',
            errorMessage: $reason,
        );
    }

    /**
     * Convert to array for logging/storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'request_id' => $this->requestId,
            'message_id' => $this->messageId,
            'status' => $this->status,
            'error_code' => $this->errorCode,
            'error_message' => $this->errorMessage,
            'cost' => $this->cost,
            'segments' => $this->segments,
        ];
    }
}

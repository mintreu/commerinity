<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\DTOs;

/**
 * Delivery Report DTO - SMS delivery status from provider.
 */
final readonly class DeliveryReport
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    /**
     * @param  bool  $success  Whether report fetch was successful
     * @param  string|null  $requestId  Provider request ID
     * @param  string|null  $messageId  Provider message ID
     * @param  string  $status  Delivery status
     * @param  string|null  $recipient  Phone number
     * @param  string|null  $deliveredAt  Delivery timestamp
     * @param  string|null  $errorCode  Error code if failed
     * @param  string|null  $errorMessage  Error description
     * @param  array<string, mixed>  $rawData  Raw provider response
     */
    public function __construct(
        public bool $success,
        public ?string $requestId = null,
        public ?string $messageId = null,
        public string $status = self::STATUS_PENDING,
        public ?string $recipient = null,
        public ?string $deliveredAt = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public array $rawData = [],
    ) {}

    /**
     * Create from Fast2SMS delivery report.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromFast2Sms(array $data): self
    {
        $status = match ($data['status'] ?? 'PENDING') {
            'DELIVERED', 'DELIVRD' => self::STATUS_DELIVERED,
            'SENT' => self::STATUS_SENT,
            'FAILED', 'UNDELIV', 'REJECTD' => self::STATUS_FAILED,
            'EXPIRED' => self::STATUS_EXPIRED,
            default => self::STATUS_PENDING,
        };

        return new self(
            success: true,
            requestId: $data['request_id'] ?? null,
            messageId: $data['message_id'] ?? null,
            status: $status,
            recipient: $data['mobile'] ?? $data['number'] ?? null,
            deliveredAt: $data['delivered_time'] ?? $data['delivered_at'] ?? null,
            errorCode: $data['error_code'] ?? null,
            errorMessage: $data['error_message'] ?? null,
            rawData: $data,
        );
    }

    /**
     * Create error response.
     */
    public static function error(string $message, ?string $requestId = null): self
    {
        return new self(
            success: false,
            requestId: $requestId,
            status: self::STATUS_FAILED,
            errorMessage: $message,
        );
    }

    /**
     * Check if delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_REJECTED, self::STATUS_EXPIRED], true);
    }

    /**
     * Check if still pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_QUEUED, self::STATUS_SENT], true);
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'request_id' => $this->requestId,
            'message_id' => $this->messageId,
            'status' => $this->status,
            'recipient' => $this->recipient,
            'delivered_at' => $this->deliveredAt,
            'error_code' => $this->errorCode,
            'error_message' => $this->errorMessage,
        ];
    }
}

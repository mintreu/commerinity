<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payout\DTOs;

/**
 * PayoutResponse DTO
 *
 * Standard response from payout operations
 */
final readonly class PayoutResponse
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REVERSED = 'reversed';

    /**
     * @param  bool  $success  Whether the operation was successful
     * @param  string  $status  Payout status
     * @param  string|null  $message  Human-readable message
     * @param  string|null  $transactionId  Our internal transaction ID
     * @param  string|null  $providerPayoutId  Provider's payout ID
     * @param  string|null  $utrNumber  UTR/Reference number
     * @param  array<string, mixed>  $metadata  Additional data
     */
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $message = null,
        public ?string $transactionId = null,
        public ?string $providerPayoutId = null,
        public ?string $utrNumber = null,
        public array $metadata = [],
    ) {}

    /**
     * Create a successful response
     */
    public static function success(
        string $status = self::STATUS_COMPLETED,
        ?string $message = null,
        ?string $transactionId = null,
        ?string $providerPayoutId = null,
        ?string $utrNumber = null,
        array $metadata = [],
    ): self {
        return new self(
            success: true,
            status: $status,
            message: $message ?? 'Payout successful',
            transactionId: $transactionId,
            providerPayoutId: $providerPayoutId,
            utrNumber: $utrNumber,
            metadata: $metadata,
        );
    }

    /**
     * Create a pending response
     */
    public static function pending(
        ?string $message = null,
        ?string $transactionId = null,
        ?string $providerPayoutId = null,
        array $metadata = [],
    ): self {
        return new self(
            success: true,
            status: self::STATUS_PENDING,
            message: $message ?? 'Payout initiated',
            transactionId: $transactionId,
            providerPayoutId: $providerPayoutId,
            metadata: $metadata,
        );
    }

    /**
     * Create a failed response
     */
    public static function failed(
        ?string $message = null,
        ?string $transactionId = null,
        array $metadata = [],
    ): self {
        return new self(
            success: false,
            status: self::STATUS_FAILED,
            message: $message ?? 'Payout failed',
            transactionId: $transactionId,
            metadata: $metadata,
        );
    }

    /**
     * Check if payout is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'message' => $this->message,
            'transaction_id' => $this->transactionId,
            'provider_payout_id' => $this->providerPayoutId,
            'utr_number' => $this->utrNumber,
            'metadata' => $this->metadata,
        ];
    }
}

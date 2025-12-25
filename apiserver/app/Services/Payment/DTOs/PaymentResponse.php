<?php

declare(strict_types=1);

namespace App\Services\Payment\DTOs;

/**
 * PaymentResponse DTO
 *
 * Standard response from payment operations
 */
final readonly class PaymentResponse
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    /**
     * @param  bool  $success  Whether the operation was successful
     * @param  string  $status  Payment status
     * @param  string|null  $message  Human-readable message
     * @param  string|null  $transactionId  Our internal transaction ID
     * @param  string|null  $providerOrderId  Provider's order ID
     * @param  string|null  $providerTransactionId  Provider's transaction ID
     * @param  string|null  $checkoutUrl  URL for payment (for redirect-based flows)
     * @param  string|null  $qrCodeUrl  QR code image URL (for UPI)
     * @param  array<string, mixed>  $metadata  Additional data
     */
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $message = null,
        public ?string $transactionId = null,
        public ?string $providerOrderId = null,
        public ?string $providerTransactionId = null,
        public ?string $checkoutUrl = null,
        public ?string $qrCodeUrl = null,
        public array $metadata = [],
    ) {}

    /**
     * Create a successful response
     */
    public static function success(
        string $status = self::STATUS_COMPLETED,
        ?string $message = null,
        ?string $transactionId = null,
        ?string $providerOrderId = null,
        ?string $providerTransactionId = null,
        array $metadata = [],
    ): self {
        return new self(
            success: true,
            status: $status,
            message: $message ?? 'Payment successful',
            transactionId: $transactionId,
            providerOrderId: $providerOrderId,
            providerTransactionId: $providerTransactionId,
            metadata: $metadata,
        );
    }

    /**
     * Create a pending response (for async payments)
     */
    public static function pending(
        ?string $message = null,
        ?string $transactionId = null,
        ?string $providerOrderId = null,
        ?string $checkoutUrl = null,
        ?string $qrCodeUrl = null,
        array $metadata = [],
    ): self {
        return new self(
            success: true,
            status: self::STATUS_PENDING,
            message: $message ?? 'Payment initiated',
            transactionId: $transactionId,
            providerOrderId: $providerOrderId,
            checkoutUrl: $checkoutUrl,
            qrCodeUrl: $qrCodeUrl,
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
            message: $message ?? 'Payment failed',
            transactionId: $transactionId,
            metadata: $metadata,
        );
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get TransactionStatusCast enum from status string
     */
    public function getStatusEnum(): \App\Casts\TransactionStatusCast
    {
        return match ($this->status) {
            self::STATUS_PENDING => \App\Casts\TransactionStatusCast::PENDING,
            self::STATUS_PROCESSING => \App\Casts\TransactionStatusCast::PROCESSING,
            self::STATUS_COMPLETED => \App\Casts\TransactionStatusCast::COMPLETED,
            self::STATUS_FAILED => \App\Casts\TransactionStatusCast::FAILED,
            self::STATUS_CANCELLED => \App\Casts\TransactionStatusCast::CANCELLED,
            self::STATUS_REFUNDED => \App\Casts\TransactionStatusCast::REFUNDED,
            default => \App\Casts\TransactionStatusCast::PENDING,
        };
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
            'provider_order_id' => $this->providerOrderId,
            'provider_transaction_id' => $this->providerTransactionId,
            'checkout_url' => $this->checkoutUrl,
            'qr_code_url' => $this->qrCodeUrl,
            'metadata' => $this->metadata,
        ];
    }
}

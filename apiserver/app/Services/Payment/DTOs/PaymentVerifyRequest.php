<?php

declare(strict_types=1);

namespace App\Services\Payment\DTOs;

/**
 * PaymentVerifyRequest DTO
 *
 * Immutable data transfer object for payment verification
 */
final readonly class PaymentVerifyRequest
{
    /**
     * @param  string  $orderId  Our internal order/transaction ID
     * @param  string|null  $providerOrderId  Provider's order ID
     * @param  string|null  $providerTransactionId  Provider's transaction/payment ID
     * @param  string|null  $providerSignature  Signature for verification
     * @param  array<string, mixed>  $providerResponse  Full provider response
     */
    public function __construct(
        public string $orderId,
        public ?string $providerOrderId = null,
        public ?string $providerTransactionId = null,
        public ?string $providerSignature = null,
        public array $providerResponse = [],
    ) {}

    /**
     * Create from array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            providerOrderId: $data['provider_order_id'] ?? null,
            providerTransactionId: $data['provider_transaction_id'] ?? null,
            providerSignature: $data['provider_signature'] ?? null,
            providerResponse: $data['provider_response'] ?? [],
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'provider_order_id' => $this->providerOrderId,
            'provider_transaction_id' => $this->providerTransactionId,
            'provider_signature' => $this->providerSignature,
            'provider_response' => $this->providerResponse,
        ];
    }
}

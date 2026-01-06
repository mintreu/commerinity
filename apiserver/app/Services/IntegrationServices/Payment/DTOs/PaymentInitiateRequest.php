<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payment\DTOs;

use App\Casts\PaymentMethodCast;

/**
 * PaymentInitiateRequest DTO
 *
 * Immutable data transfer object for payment initiation
 */
final readonly class PaymentInitiateRequest
{
    /**
     * @param  int  $amountInPaisa  Amount in smallest currency unit
     * @param  string  $currency  ISO currency code (INR, USD, etc.)
     * @param  PaymentMethodCast  $method  Payment method to use
     * @param  int  $userId  User making the payment
     * @param  int  $walletId  Wallet to debit/credit
     * @param  string  $transactionId  Unique transaction identifier
     * @param  string|null  $customerName  Customer's full name
     * @param  string|null  $customerEmail  Customer's email address
     * @param  string|null  $customerPhone  Customer's phone number (E.164 format)
     * @param  string|null  $purpose  Payment purpose (subscription, order, etc.)
     * @param  string|null  $description  Human-readable description
     * @param  array<string, mixed>  $metadata  Additional metadata
     * @param  string|null  $callbackUrl  URL for payment callback
     * @param  int|null  $expiresInMinutes  Payment link expiry
     */
    public function __construct(
        public int $amountInPaisa,
        public string $currency,
        public PaymentMethodCast $method,
        public ?string $userFingerprint = null,
        public int $userId,
        public int $walletId,
        public string $transactionId,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
        public ?string $purpose = null,
        public ?string $description = null,
        public array $metadata = [],
        public ?string $callbackUrl = null,
        public ?int $expiresInMinutes = 30,
    ) {}

    /**
     * Create from array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amountInPaisa: (int) $data['amount'],
            currency: $data['currency'] ?? 'INR',
            method: $data['method'] instanceof PaymentMethodCast
                ? $data['method']
                : PaymentMethodCast::from($data['method']),
            userFingerprint: (string) ($data['user_fingerprint'] ?? $data['user_id']),
            userId: (int) $data['user_id'],
            walletId: (int) $data['wallet_id'],
            transactionId: $data['transaction_id'],
            customerName: $data['customer_name'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            purpose: $data['purpose'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? [],
            callbackUrl: $data['callback_url'] ?? null,
            expiresInMinutes: $data['expires_in_minutes'] ?? 30,
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
            'amount' => $this->amountInPaisa,
            'currency' => $this->currency,
            'method' => $this->method->value,
            'user_id' => $this->userId,
            'user_fingerprint' => $this->userFingerprint,
            'wallet_id' => $this->walletId,
            'transaction_id' => $this->transactionId,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'purpose' => $this->purpose,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'callback_url' => $this->callbackUrl,
            'expires_in_minutes' => $this->expiresInMinutes,
        ];
    }

    /**
     * Get amount in rupees (for providers that need rupees)
     */
    public function getAmountInRupees(): float
    {
        return $this->amountInPaisa / 100;
    }
}

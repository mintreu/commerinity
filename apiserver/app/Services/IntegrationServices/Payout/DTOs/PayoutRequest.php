<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payout\DTOs;

use App\Casts\PaymentMethodCast;

/**
 * PayoutRequest DTO
 *
 * Immutable data transfer object for payout requests
 */
final readonly class PayoutRequest
{
    /**
     * @param  int  $amountInPaisa  Amount in smallest currency unit
     * @param  string  $currency  ISO currency code (INR, USD, etc.)
     * @param  PaymentMethodCast  $method  Payout method
     * @param  int  $userId  User receiving the payout
     * @param  int  $walletId  Wallet to debit
     * @param  int  $beneficiaryAccountId  Beneficiary bank/UPI account
     * @param  string  $transactionId  Unique transaction identifier
     * @param  string|null  $purpose  Payout purpose
     * @param  string|null  $description  Human-readable description
     * @param  array<string, mixed>  $metadata  Additional metadata
     */
    public function __construct(
        public int $amountInPaisa,
        public string $currency,
        public PaymentMethodCast $method,
        public int $userId,
        public int $walletId,
        public int $beneficiaryAccountId,
        public string $transactionId,
        public ?string $purpose = null,
        public ?string $description = null,
        public array $metadata = [],
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
            userId: (int) $data['user_id'],
            walletId: (int) $data['wallet_id'],
            beneficiaryAccountId: (int) $data['beneficiary_account_id'],
            transactionId: $data['transaction_id'],
            purpose: $data['purpose'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? [],
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
            'wallet_id' => $this->walletId,
            'beneficiary_account_id' => $this->beneficiaryAccountId,
            'transaction_id' => $this->transactionId,
            'purpose' => $this->purpose,
            'description' => $this->description,
            'metadata' => $this->metadata,
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

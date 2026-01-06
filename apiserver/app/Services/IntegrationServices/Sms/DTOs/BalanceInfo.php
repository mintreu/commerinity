<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\DTOs;

/**
 * Balance Info DTO - Provider wallet balance information.
 */
final readonly class BalanceInfo
{
    /**
     * @param  bool  $success  Whether balance check was successful
     * @param  float  $balance  Current wallet balance
     * @param  float  $perSmsCost  Cost per SMS
     * @param  int  $canSendCount  Number of SMS that can be sent with current balance
     * @param  bool  $isLow  Whether balance is below threshold
     * @param  float  $threshold  Low balance threshold
     * @param  string|null  $errorMessage  Error message if check failed
     * @param  \DateTimeInterface|null  $checkedAt  When balance was checked
     */
    public function __construct(
        public bool $success,
        public float $balance = 0.0,
        public float $perSmsCost = 0.25,
        public int $canSendCount = 0,
        public bool $isLow = false,
        public float $threshold = 10.0,
        public ?string $errorMessage = null,
        public ?\DateTimeInterface $checkedAt = null,
    ) {}

    /**
     * Create from successful balance check.
     */
    public static function fromBalance(
        float $balance,
        float $perSmsCost = 0.25,
        float $threshold = 10.0,
    ): self {
        $canSend = $perSmsCost > 0 ? (int) floor($balance / $perSmsCost) : 0;

        return new self(
            success: true,
            balance: $balance,
            perSmsCost: $perSmsCost,
            canSendCount: $canSend,
            isLow: $balance < $threshold,
            threshold: $threshold,
            checkedAt: now(),
        );
    }

    /**
     * Create from error.
     */
    public static function error(string $message): self
    {
        return new self(
            success: false,
            errorMessage: $message,
            checkedAt: now(),
        );
    }

    /**
     * Check if can send a specific number of SMS.
     */
    public function canSend(int $count = 1): bool
    {
        return $this->success && $this->canSendCount >= $count;
    }

    /**
     * Get required balance for a specific count.
     */
    public function getRequiredBalance(int $count = 1): float
    {
        return $count * $this->perSmsCost;
    }

    /**
     * Get balance shortage for a specific count.
     */
    public function getShortage(int $count = 1): float
    {
        $required = $this->getRequiredBalance($count);

        return max(0, $required - $this->balance);
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
            'balance' => $this->balance,
            'per_sms_cost' => $this->perSmsCost,
            'can_send_count' => $this->canSendCount,
            'is_low' => $this->isLow,
            'threshold' => $this->threshold,
            'error_message' => $this->errorMessage,
            'checked_at' => $this->checkedAt?->format('Y-m-d H:i:s'),
        ];
    }
}

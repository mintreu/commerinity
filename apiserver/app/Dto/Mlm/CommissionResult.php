<?php

declare(strict_types=1);

namespace App\Dto\Mlm;

use App\Contracts\Mlm\CommissionTrigger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Data Transfer Object for calculated commission
 *
 * Immutable value object containing all commission calculation results.
 * Used to pass data between calculator → processor → model creation.
 */
final readonly class CommissionResult
{
    public function __construct(
        public int $recipientId,
        public ?int $genealogyId,
        public ?int $fromUserId,
        public string $type,
        public ?int $level,
        public float $ratePercent,
        public int $baseAmount,
        public int $grossAmount,
        public int $tdsAmount,
        public int $adminFee,
        public int $netAmount,
        public string $description,
        public array $metadata,
        public ?string $commissionableType = null,
        public ?int $commissionableId = null,
    ) {}

    /**
     * Create from commission trigger
     */
    public static function make(
        int $recipientId,
        string $type,
        int $grossAmount,
        CommissionTrigger $trigger,
        ?int $genealogyId = null,
        ?int $level = null,
        float $ratePercent = 0,
        int $baseAmount = 0,
        int $tdsAmount = 0,
        int $adminFee = 0,
        string $description = '',
        array $metadata = [],
    ): self {
        $model = $trigger->getModel();

        return new self(
            recipientId: $recipientId,
            genealogyId: $genealogyId,
            fromUserId: $trigger->getTriggeringUserId(),
            type: $type,
            level: $level,
            ratePercent: $ratePercent,
            baseAmount: $baseAmount ?: $trigger->getCommissionableAmount(),
            grossAmount: $grossAmount,
            tdsAmount: $tdsAmount,
            adminFee: $adminFee,
            netAmount: $grossAmount - $tdsAmount - $adminFee,
            description: $description,
            metadata: $metadata,
            commissionableType: get_class($model),
            commissionableId: $model->getKey(),
        );
    }

    /**
     * Create for simple bonus (no trigger model)
     */
    public static function bonus(
        int $recipientId,
        string $type,
        int $amount,
        string $description,
        ?int $genealogyId = null,
        ?int $fromUserId = null,
        array $metadata = [],
    ): self {
        return new self(
            recipientId: $recipientId,
            genealogyId: $genealogyId,
            fromUserId: $fromUserId,
            type: $type,
            level: null,
            ratePercent: 0,
            baseAmount: $amount,
            grossAmount: $amount,
            tdsAmount: 0,
            adminFee: 0,
            netAmount: $amount,
            description: $description,
            metadata: $metadata,
        );
    }

    /**
     * Create with deductions applied
     */
    public function withDeductions(int $tdsAmount, int $adminFee = 0): self
    {
        return new self(
            recipientId: $this->recipientId,
            genealogyId: $this->genealogyId,
            fromUserId: $this->fromUserId,
            type: $this->type,
            level: $this->level,
            ratePercent: $this->ratePercent,
            baseAmount: $this->baseAmount,
            grossAmount: $this->grossAmount,
            tdsAmount: $tdsAmount,
            adminFee: $adminFee,
            netAmount: $this->grossAmount - $tdsAmount - $adminFee,
            description: $this->description,
            metadata: $this->metadata,
            commissionableType: $this->commissionableType,
            commissionableId: $this->commissionableId,
        );
    }

    /**
     * Convert to array for model creation
     *
     * Note: Includes generated values that would normally come from model events
     * to ensure compatibility across all contexts (tests, queues, etc.)
     */
    public function toArray(): array
    {
        return [
            'uuid' => 'COM-'.Str::upper(Str::random(12)),
            'user_id' => $this->recipientId,
            'genealogy_id' => $this->genealogyId,
            'from_user_id' => $this->fromUserId,
            'commissionable_type' => $this->commissionableType,
            'commissionable_id' => $this->commissionableId,
            'type' => $this->type,
            'level' => $this->level,
            'rate_percent' => $this->ratePercent,
            'base_amount' => $this->baseAmount,
            'gross_amount' => $this->grossAmount,
            'tds_amount' => $this->tdsAmount,
            'admin_fee' => $this->adminFee,
            'net_amount' => $this->netAmount,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'commission_date' => now()->toDateString(),
            'period_key' => now()->format('Y-m'),
        ];
    }
}

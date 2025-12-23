<?php

declare(strict_types=1);

namespace App\Services\Mlm\Calculators;

use App\Casts\CommissionTypeCast;
use App\Contracts\Mlm\CommissionCalculator;
use App\Contracts\Mlm\CommissionTrigger;
use App\Dto\Mlm\CommissionResult;
use App\Services\Mlm\MlmConfigService;
use Illuminate\Support\Collection;

/**
 * Base Commission Calculator
 *
 * Provides common functionality for all commission calculators.
 * Extend this class to create specific calculators.
 */
abstract class BaseCommissionCalculator implements CommissionCalculator
{
    protected MlmConfigService $configService;

    public function __construct(?MlmConfigService $configService = null)
    {
        $this->configService = $configService ?? new MlmConfigService;
    }

    /**
     * Get the commission type this calculator handles
     */
    abstract public function getCommissionType(): string;

    /**
     * Get trigger types this calculator supports
     * Override to restrict to specific triggers
     */
    protected function getSupportedTriggerTypes(): array
    {
        return []; // Empty = supports all triggers
    }

    /**
     * Perform the actual commission calculation
     *
     * @return Collection<int, CommissionResult>
     */
    abstract protected function doCalculate(CommissionTrigger $trigger): Collection;

    /**
     * Check if this calculator should process the given trigger
     */
    public function supports(CommissionTrigger $trigger): bool
    {
        $supportedTypes = $this->getSupportedTriggerTypes();

        if (empty($supportedTypes)) {
            return true;
        }

        return in_array($trigger->getTriggerType(), $supportedTypes, true);
    }

    /**
     * Check if this commission type is enabled
     */
    public function isEnabled(): bool
    {
        return $this->configService->isCommissionTypeEnabled($this->getCommissionType());
    }

    /**
     * Calculate commissions with enable check
     *
     * @return Collection<int, CommissionResult>
     */
    public function calculate(CommissionTrigger $trigger): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        if (! $this->supports($trigger)) {
            return collect();
        }

        return $this->doCalculate($trigger);
    }

    /**
     * Get calculator priority (default 50)
     */
    public function getPriority(): int
    {
        return 50;
    }

    // ========================================
    // Helper Methods for Subclasses
    // ========================================

    /**
     * Calculate percentage amount
     */
    protected function calculatePercent(int $baseAmount, float $percent): int
    {
        return (int) round($baseAmount * ($percent / 100));
    }

    /**
     * Get commission rate from config or stage
     */
    protected function getRateFromConfig(string $key, float $default = 0): float
    {
        return (float) config($key, $default);
    }

    /**
     * Create result with standard description
     */
    protected function createResult(
        int $recipientId,
        int $grossAmount,
        CommissionTrigger $trigger,
        ?int $genealogyId = null,
        ?int $level = null,
        float $ratePercent = 0,
        int $baseAmount = 0,
        array $metadata = [],
    ): CommissionResult {
        $description = $this->buildDescription($level, $ratePercent);

        return CommissionResult::make(
            recipientId: $recipientId,
            type: $this->getCommissionType(),
            grossAmount: $grossAmount,
            trigger: $trigger,
            genealogyId: $genealogyId,
            level: $level,
            ratePercent: $ratePercent,
            baseAmount: $baseAmount ?: $trigger->getCommissionableAmount(),
            description: $description,
            metadata: $metadata,
        );
    }

    /**
     * Build description based on commission type
     */
    protected function buildDescription(?int $level = null, float $ratePercent = 0): string
    {
        $label = CommissionTypeCast::label($this->getCommissionType());

        if ($level !== null && $ratePercent > 0) {
            return "{$label} - Level {$level} ({$ratePercent}%)";
        }

        if ($ratePercent > 0) {
            return "{$label} ({$ratePercent}%)";
        }

        return $label;
    }
}

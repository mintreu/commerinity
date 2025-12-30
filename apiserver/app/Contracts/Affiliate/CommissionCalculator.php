<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

use Illuminate\Support\Collection;

/**
 * Contract for commission calculators (Strategy Pattern)
 *
 * Each commission type has its own calculator implementation:
 * - SponsorBonusCalculator
 * - LevelCommissionCalculator
 * - OriginatorJoiningCalculator
 * - TaskCompletionCalculator
 * - etc.
 */
interface CommissionCalculator
{
    /**
     * Get the commission type this calculator handles
     */
    public function getCommissionType(): string;

    /**
     * Check if this calculator should process the given trigger
     */
    public function supports(CommissionTrigger $trigger): bool;

    /**
     * Check if this commission type is enabled
     */
    public function isEnabled(): bool;

    /**
     * Calculate commissions for a trigger event
     *
     * @return Collection<int, CommissionResult>
     */
    public function calculate(CommissionTrigger $trigger): Collection;

    /**
     * Get calculator priority (higher = processed first)
     */
    public function getPriority(): int;
}

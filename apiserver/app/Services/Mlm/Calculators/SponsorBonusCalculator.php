<?php

declare(strict_types=1);

namespace App\Services\Mlm\Calculators;

use App\Casts\CommissionTypeCast;
use App\Contracts\Mlm\CommissionTrigger;
use App\Models\Membership\Stage;
use App\Models\Mlm\MlmGenealogy;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sponsor Bonus Calculator
 *
 * Calculates one-time bonus for direct sponsor when new member joins.
 * Rate comes from Stage model (sponsor_bonus JSON).
 */
final class SponsorBonusCalculator extends BaseCommissionCalculator
{
    public function getCommissionType(): string
    {
        return CommissionTypeCast::SPONSOR_BONUS;
    }

    protected function getSupportedTriggerTypes(): array
    {
        return ['subscription', 'joining'];
    }

    public function getPriority(): int
    {
        return 100; // Process first
    }

    protected function doCalculate(CommissionTrigger $trigger): Collection
    {
        $results = collect();

        $triggeringUser = User::find($trigger->getTriggeringUserId());
        if (! $triggeringUser || ! $triggeringUser->parent_id) {
            return $results;
        }

        $sponsor = $triggeringUser->parent;
        if (! $sponsor) {
            return $results;
        }

        $sponsorGenealogy = MlmGenealogy::forUser($sponsor->id);
        if (! $sponsorGenealogy?->is_active) {
            return $results;
        }

        // Get rate from context (Stage) or config
        $context = $trigger->getCommissionContext();
        $stage = isset($context['stage_id']) ? Stage::find($context['stage_id']) : null;

        $baseAmount = $trigger->getCommissionableAmount();
        $bonusAmount = $stage
            ? $stage->getSponsorBonusAmount($baseAmount)
            : $this->calculatePercent($baseAmount, $this->getRateFromConfig('mlm.default_sponsor_bonus_percent', 10));

        if ($bonusAmount <= 0) {
            return $results;
        }

        $results->push($this->createResult(
            recipientId: $sponsor->id,
            grossAmount: $bonusAmount,
            trigger: $trigger,
            genealogyId: $sponsorGenealogy->id,
            ratePercent: $stage?->sponsor_bonus['value'] ?? 0,
            baseAmount: $baseAmount,
        ));

        return $results;
    }
}

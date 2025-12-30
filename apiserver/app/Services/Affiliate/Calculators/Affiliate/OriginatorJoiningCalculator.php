<?php

declare(strict_types=1);

namespace App\Services\Affiliate\Calculators\Affiliate;

use App\Casts\CommissionTypeCast;
use App\Contracts\Affiliate\CommissionTrigger;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use App\Services\Affiliate\Calculators\BaseCommissionCalculator;
use Illuminate\Support\Collection;

/**
 * Originator Joining Commission Calculator
 *
 * Calculates one-time commission for originator (agent/advisor) when
 * an originated user joins/subscribes.
 *
 * Rate comes from config (affiliate.originator_commissions.joining_commission).
 *
 * Triggers: subscription, joining
 */
final class OriginatorJoiningCalculator extends BaseCommissionCalculator
{
    public function getCommissionType(): string
    {
        return CommissionTypeCast::ORIGINATOR_JOINING;
    }

    protected function getSupportedTriggerTypes(): array
    {
        return ['subscription', 'joining'];
    }

    public function getPriority(): int
    {
        return 80; // Process after level commission
    }

    public function isEnabled(): bool
    {
        return $this->configService->isOriginatorCommissionsEnabled()
            && $this->configService->isCommissionTypeEnabled($this->getCommissionType());
    }

    protected function doCalculate(CommissionTrigger $trigger): Collection
    {
        $results = collect();

        $triggeringUser = User::find($trigger->getTriggeringUserId());
        if (! $triggeringUser) {
            return $results;
        }

        // Get originator from user
        $originatorId = $triggeringUser->originator_id;
        if (! $originatorId) {
            return $results;
        }

        $originator = User::find($originatorId);
        if (! $originator) {
            return $results;
        }

        // Check if originator has active genealogy
        $originatorGenealogy = AffiliateGenealogy::forUser($originator->id);
        if (! $originatorGenealogy?->is_active) {
            return $results;
        }

        // Get originator joining commission config
        $config = $this->configService->getOriginatorJoiningConfig();
        if (! $config['enabled']) {
            return $results;
        }

        $baseAmount = $trigger->getCommissionableAmount();
        $commissionAmount = $this->calculateOriginatorCommission($baseAmount, $config);

        if ($commissionAmount <= 0) {
            return $results;
        }

        $ratePercent = $config['type'] === 'percent' ? (float) $config['value'] : 0;

        $results->push($this->createResult(
            recipientId: $originator->id,
            grossAmount: $commissionAmount,
            trigger: $trigger,
            genealogyId: $originatorGenealogy->id,
            ratePercent: $ratePercent,
            baseAmount: $baseAmount,
            metadata: [
                'originated_user_id' => $triggeringUser->id,
                'originator_type' => $triggeringUser->originator_type,
                'commission_type' => $config['type'],
                'commission_value' => $config['value'],
            ],
        ));

        return $results;
    }

    /**
     * Calculate originator commission based on config
     *
     * @param  array{type: string, value: float}  $config
     */
    private function calculateOriginatorCommission(int $baseAmount, array $config): int
    {
        $type = $config['type'] ?? 'percent';
        $value = (float) ($config['value'] ?? 0);

        if ($type === 'percent') {
            return $this->calculatePercent($baseAmount, $value);
        }

        // Fixed amount (stored in paisa in config)
        return (int) $value;
    }
}

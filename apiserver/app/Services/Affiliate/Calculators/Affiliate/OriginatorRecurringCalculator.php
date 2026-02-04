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
 * Originator Recurring Commission Calculator
 *
 * Calculates recurring commission for originator (agent/advisor) based on
 * originated user's withdrawals or monthly income.
 *
 * Two modes (configurable):
 * - on_withdrawal: Triggers when user withdraws funds
 * - monthly: Triggers on monthly income calculation
 *
 * Rate comes from config (affiliate.originator_commissions.recurring_commission).
 *
 * Triggers: withdrawal, monthly_settlement, income
 */
final class OriginatorRecurringCalculator extends BaseCommissionCalculator
{
    public function getCommissionType(): string
    {
        return CommissionTypeCast::ORIGINATOR_RECURRING->value;
    }

    protected function getSupportedTriggerTypes(): array
    {
        return ['withdrawal', 'monthly_settlement', 'income'];
    }

    public function getPriority(): int
    {
        return 70; // Process after originator joining
    }

    public function isEnabled(): bool
    {
        return $this->configService->isOriginatorCommissionsEnabled()
            && $this->configService->isCommissionTypeEnabled($this->getCommissionType());
    }

    protected function doCalculate(CommissionTrigger $trigger): Collection
    {
        $results = collect();

        // Get recurring commission config
        $config = $this->configService->getOriginatorRecurringConfig();
        if (! $config['enabled']) {
            return $results;
        }

        // Check if trigger type matches configured frequency
        $frequency = $config['frequency'] ?? 'on_withdrawal';
        if (! $this->matchesFrequency($trigger->getTriggerType(), $frequency)) {
            return $results;
        }

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

        $baseAmount = $trigger->getCommissionableAmount();
        $commissionAmount = $this->calculateRecurringCommission($baseAmount, $config);

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
                'frequency' => $frequency,
                'trigger_type' => $trigger->getTriggerType(),
                'commission_type' => $config['type'],
                'commission_value' => $config['value'],
            ],
        ));

        return $results;
    }

    /**
     * Check if trigger type matches configured frequency
     */
    private function matchesFrequency(string $triggerType, string $frequency): bool
    {
        return match ($frequency) {
            'on_withdrawal' => $triggerType === 'withdrawal',
            'monthly' => in_array($triggerType, ['monthly_settlement', 'income'], true),
            default => false,
        };
    }

    /**
     * Calculate recurring commission based on config
     *
     * @param  array{type: string, value: float}  $config
     */
    private function calculateRecurringCommission(int $baseAmount, array $config): int
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

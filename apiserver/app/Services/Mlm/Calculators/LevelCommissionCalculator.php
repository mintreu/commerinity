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
 * Level Commission Calculator
 *
 * Calculates multi-level commissions for ancestors in the MLM tree.
 * Rate comes from Stage model (commission_rates JSON) for each depth level.
 * Supports up to 4 levels (matrix depth) with configurable rates.
 */
final class LevelCommissionCalculator extends BaseCommissionCalculator
{
    private readonly int $maxDepth;

    public function __construct()
    {
        parent::__construct();
        $this->maxDepth = (int) config('mlm.matrix.depth', 4);
    }

    public function getCommissionType(): string
    {
        return CommissionTypeCast::LEVEL_COMMISSION;
    }

    protected function getSupportedTriggerTypes(): array
    {
        return ['subscription', 'joining', 'purchase'];
    }

    public function getPriority(): int
    {
        return 90; // Process after sponsor bonus
    }

    protected function doCalculate(CommissionTrigger $trigger): Collection
    {
        $results = collect();

        $triggeringUser = User::find($trigger->getTriggeringUserId());
        if (! $triggeringUser) {
            return $results;
        }

        // Get stage from context for commission rates
        $context = $trigger->getCommissionContext();
        $stage = isset($context['stage_id']) ? Stage::find($context['stage_id']) : null;

        if (! $stage) {
            return $results;
        }

        $baseAmount = $trigger->getCommissionableAmount();

        // Traverse upline and calculate commissions for each level
        $ancestors = $this->getActiveAncestors($triggeringUser, $this->maxDepth);

        foreach ($ancestors as $depth => $ancestor) {
            $level = $depth + 1; // Depth is 0-indexed, level is 1-indexed

            // Get rate for this level from stage configuration
            $ratePercent = $stage->getCommissionRate($level);

            if ($ratePercent <= 0) {
                continue;
            }

            // Check if ancestor has active genealogy
            $ancestorGenealogy = MlmGenealogy::forUser($ancestor->id);
            if (! $ancestorGenealogy?->is_active) {
                continue;
            }

            $commissionAmount = $this->calculatePercent($baseAmount, $ratePercent);

            if ($commissionAmount <= 0) {
                continue;
            }

            $results->push($this->createResult(
                recipientId: $ancestor->id,
                grossAmount: $commissionAmount,
                trigger: $trigger,
                genealogyId: $ancestorGenealogy->id,
                level: $level,
                ratePercent: $ratePercent,
                baseAmount: $baseAmount,
                metadata: [
                    'from_user_id' => $triggeringUser->id,
                    'depth' => $depth,
                ],
            ));
        }

        return $results;
    }

    /**
     * Get active ancestors up to max depth
     *
     * @return Collection<int, User>
     */
    private function getActiveAncestors(User $user, int $maxDepth): Collection
    {
        $ancestors = collect();
        $current = $user->parent;
        $depth = 0;

        while ($current && $depth < $maxDepth) {
            $ancestors->put($depth, $current);
            $current = $current->parent;
            $depth++;
        }

        return $ancestors;
    }
}

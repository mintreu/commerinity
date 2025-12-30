<?php

declare(strict_types=1);

namespace App\Services\Affiliate\Calculators\Task;

use App\Casts\CommissionTypeCast;
use App\Contracts\Affiliate\CommissionTrigger;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use App\Services\Affiliate\Calculators\BaseCommissionCalculator;
use Illuminate\Support\Collection;

/**
 * Task Completion Commission Calculator
 *
 * Calculates commission when a user completes a task.
 * Tasks can be anything - KPIs, milestones, challenges, etc.
 *
 * Task reward comes from trigger context (task configuration).
 *
 * Triggers: task_completion, task
 */
final class TaskCompletionCalculator extends BaseCommissionCalculator
{
    public function getCommissionType(): string
    {
        return CommissionTypeCast::TASK_COMPLETION;
    }

    protected function getSupportedTriggerTypes(): array
    {
        return ['task_completion', 'task'];
    }

    public function getPriority(): int
    {
        return 60; // Process after originator commissions
    }

    public function isEnabled(): bool
    {
        return (bool) config('affiliate.task_commissions.enabled', false);
    }

    protected function doCalculate(CommissionTrigger $trigger): Collection
    {
        $results = collect();

        $triggeringUser = User::find($trigger->getTriggeringUserId());
        if (! $triggeringUser) {
            return $results;
        }

        // Check if user has active genealogy
        $genealogy = AffiliateGenealogy::forUser($triggeringUser->id);
        if (! $genealogy?->is_active) {
            return $results;
        }

        // Get task reward from context
        $context = $trigger->getCommissionContext();
        $taskReward = $this->calculateTaskReward($trigger, $context);

        if ($taskReward <= 0) {
            return $results;
        }

        $results->push($this->createResult(
            recipientId: $triggeringUser->id,
            grossAmount: $taskReward,
            trigger: $trigger,
            genealogyId: $genealogy->id,
            baseAmount: $taskReward,
            metadata: [
                'task_id' => $context['task_id'] ?? null,
                'task_type' => $context['task_type'] ?? 'generic',
                'task_name' => $context['task_name'] ?? 'Task Completion',
                'completion_data' => $context['completion_data'] ?? [],
            ],
        ));

        return $results;
    }

    /**
     * Calculate task reward from trigger and context
     *
     * @param  array<string, mixed>  $context
     */
    private function calculateTaskReward(CommissionTrigger $trigger, array $context): int
    {
        // Priority: context reward > trigger amount > config default
        if (isset($context['reward_amount']) && $context['reward_amount'] > 0) {
            return (int) $context['reward_amount'];
        }

        $triggerAmount = $trigger->getCommissionableAmount();
        if ($triggerAmount > 0) {
            return $triggerAmount;
        }

        // Fallback to config default
        return (int) config('affiliate.task_commissions.default_reward', 0);
    }
}

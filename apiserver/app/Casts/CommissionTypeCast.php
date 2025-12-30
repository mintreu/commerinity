<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Commission Type Cast
 *
 * Defines the types of commissions in the Affiliate system.
 */
final class CommissionTypeCast implements CastsAttributes
{
    // ========================================
    // COMMISSION TYPES
    // ========================================

    /** One-time bonus for direct referral */
    public const SPONSOR_BONUS = 'sponsor_bonus';

    /** Commission based on network depth (level 1-4) */
    public const LEVEL_COMMISSION = 'level_commission';

    /** Percentage of downline's earnings */
    public const MATCHING_BONUS = 'matching_bonus';

    /** Bonus for reaching a new level (2/3/4) */
    public const LEVEL_ACHIEVEMENT = 'level_achievement';

    /** Distribution from global pool */
    public const POOL_BONUS = 'pool_bonus';

    /** Commission on product purchases */
    public const PURCHASE_COMMISSION = 'purchase_commission';

    /** Bonus on subscription renewal */
    public const RENEWAL_BONUS = 'renewal_bonus';

    /** Manual adjustment by admin */
    public const ADJUSTMENT = 'adjustment';

    /** Reversal/clawback of previous commission */
    public const REVERSAL = 'reversal';

    // ========================================
    // ORIGINATOR COMMISSION TYPES
    // ========================================

    /** Originator commission on user joining/subscription */
    public const ORIGINATOR_JOINING = 'originator_joining';

    /** Recurring originator commission (on withdrawal/monthly) */
    public const ORIGINATOR_RECURRING = 'originator_recurring';

    /** Agent/Advisor salary payout (target-based) */
    public const AGENT_SALARY = 'agent_salary';

    /** Income deduction from member earnings (for agent fund) */
    public const INCOME_DEDUCTION = 'income_deduction';

    // ========================================
    // TASK/ACTIVITY BASED COMMISSION TYPES
    // ========================================

    /** Commission for completing a task/activity */
    public const TASK_COMPLETION = 'task_completion';

    /** Commission for achieving a milestone/goal */
    public const MILESTONE_BONUS = 'milestone_bonus';

    /** Commission for referral conversion (non-Affiliate referral) */
    public const REFERRAL_BONUS = 'referral_bonus';

    /** Commission for performance/KPI achievement */
    public const PERFORMANCE_BONUS = 'performance_bonus';

    /** Custom commission type (extensible via metadata) */
    public const CUSTOM = 'custom';

    /**
     * All valid commission types
     */
    public static function values(): array
    {
        return [
            // Member (Affiliate tree)
            self::SPONSOR_BONUS,
            self::LEVEL_COMMISSION,
            self::MATCHING_BONUS,
            self::LEVEL_ACHIEVEMENT,
            self::POOL_BONUS,
            self::PURCHASE_COMMISSION,
            self::RENEWAL_BONUS,
            // Administrative
            self::ADJUSTMENT,
            self::REVERSAL,
            // Originator
            self::ORIGINATOR_JOINING,
            self::ORIGINATOR_RECURRING,
            self::AGENT_SALARY,
            self::INCOME_DEDUCTION,
            // Task/Activity based
            self::TASK_COMPLETION,
            self::MILESTONE_BONUS,
            self::REFERRAL_BONUS,
            self::PERFORMANCE_BONUS,
            self::CUSTOM,
        ];
    }

    /**
     * Get human-readable labels
     */
    public static function labels(): array
    {
        return [
            self::SPONSOR_BONUS => 'Sponsor Bonus',
            self::LEVEL_COMMISSION => 'Level Commission',
            self::MATCHING_BONUS => 'Matching Bonus',
            self::LEVEL_ACHIEVEMENT => 'Level Achievement',
            self::POOL_BONUS => 'Pool Bonus',
            self::PURCHASE_COMMISSION => 'Purchase Commission',
            self::RENEWAL_BONUS => 'Renewal Bonus',
            self::ADJUSTMENT => 'Adjustment',
            self::REVERSAL => 'Reversal',
            self::ORIGINATOR_JOINING => 'Originator Joining Commission',
            self::ORIGINATOR_RECURRING => 'Originator Recurring Commission',
            self::AGENT_SALARY => 'Agent Salary',
            self::INCOME_DEDUCTION => 'Income Deduction',
            self::TASK_COMPLETION => 'Task Completion',
            self::MILESTONE_BONUS => 'Milestone Bonus',
            self::REFERRAL_BONUS => 'Referral Bonus',
            self::PERFORMANCE_BONUS => 'Performance Bonus',
            self::CUSTOM => 'Custom Commission',
        ];
    }

    /**
     * Get member commission types (Affiliate tree based)
     */
    public static function memberTypes(): array
    {
        return [
            self::SPONSOR_BONUS,
            self::LEVEL_COMMISSION,
            self::MATCHING_BONUS,
            self::LEVEL_ACHIEVEMENT,
            self::POOL_BONUS,
            self::PURCHASE_COMMISSION,
            self::RENEWAL_BONUS,
        ];
    }

    /**
     * Get originator commission types (agent/advisor based)
     */
    public static function originatorTypes(): array
    {
        return [
            self::ORIGINATOR_JOINING,
            self::ORIGINATOR_RECURRING,
            self::AGENT_SALARY,
        ];
    }

    /**
     * Get task/activity based commission types
     */
    public static function taskTypes(): array
    {
        return [
            self::TASK_COMPLETION,
            self::MILESTONE_BONUS,
            self::REFERRAL_BONUS,
            self::PERFORMANCE_BONUS,
            self::CUSTOM,
        ];
    }

    /**
     * Get deduction types (reduces member earnings)
     */
    public static function deductionTypes(): array
    {
        return [
            self::INCOME_DEDUCTION,
            self::REVERSAL,
        ];
    }

    /**
     * Get config key for a commission type (for enable/disable check)
     */
    public static function configKey(string $type): ?string
    {
        return match ($type) {
            self::SPONSOR_BONUS => 'affiliate.member_commissions.sponsor_bonus.enabled',
            self::LEVEL_COMMISSION => 'affiliate.member_commissions.level_commission.enabled',
            self::MATCHING_BONUS => 'affiliate.member_commissions.matching_bonus.enabled',
            self::LEVEL_ACHIEVEMENT => 'affiliate.member_commissions.level_achievement.enabled',
            self::POOL_BONUS => 'affiliate.member_commissions.pool_bonus.enabled',
            self::PURCHASE_COMMISSION => 'affiliate.member_commissions.purchase_commission.enabled',
            self::RENEWAL_BONUS => 'affiliate.member_commissions.renewal_bonus.enabled',
            self::ORIGINATOR_JOINING => 'affiliate.originator_commissions.joining_commission.enabled',
            self::ORIGINATOR_RECURRING => 'affiliate.originator_commissions.recurring_commission.enabled',
            self::AGENT_SALARY => 'affiliate.agent_salary.enabled',
            self::INCOME_DEDUCTION => 'affiliate.income_deduction.enabled',
            self::TASK_COMPLETION => 'affiliate.task_commissions.task_completion.enabled',
            self::MILESTONE_BONUS => 'affiliate.task_commissions.milestone_bonus.enabled',
            self::REFERRAL_BONUS => 'affiliate.task_commissions.referral_bonus.enabled',
            self::PERFORMANCE_BONUS => 'affiliate.task_commissions.performance_bonus.enabled',
            self::CUSTOM => 'affiliate.task_commissions.custom.enabled',
            default => null,
        };
    }

    /**
     * Check if a commission type is enabled in config
     */
    public static function isEnabled(string $type): bool
    {
        $key = self::configKey($type);

        if ($key === null) {
            return true; // Types without config key are always enabled
        }

        return (bool) config($key, false);
    }

    /**
     * Get label for a specific type
     */
    public static function label(string $type): string
    {
        return self::labels()[$type] ?? $type;
    }

    /**
     * Check if type is a positive commission (adds to balance)
     */
    public function isPositive(): bool
    {
        return ! in_array($this->value, [self::REVERSAL], true);
    }

    /**
     * Check if type requires approval
     */
    public function requiresApproval(): bool
    {
        return in_array($this->value, [
            self::ADJUSTMENT,
            self::POOL_BONUS,
        ], true);
    }

    /**
     * Check if type is one-time (not recurring)
     */
    public function isOneTime(): bool
    {
        return in_array($this->value, [
            self::SPONSOR_BONUS,
            self::LEVEL_ACHIEVEMENT,
        ], true);
    }

    // ========================================
    // CAST IMPLEMENTATION
    // ========================================

    private string $value;

    public function __construct(string $value = self::LEVEL_COMMISSION)
    {
        if (! in_array($value, self::values(), true)) {
            throw new InvalidArgumentException("Invalid commission type: {$value}");
        }
        $this->value = $value;
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): self
    {
        return new self($value ?? self::LEVEL_COMMISSION);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof self) {
            return $value->value;
        }

        if (is_string($value) && in_array($value, self::values(), true)) {
            return $value;
        }

        throw new InvalidArgumentException("Invalid commission type: {$value}");
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

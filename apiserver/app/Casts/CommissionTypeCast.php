<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Commission Type Enum (Filament v4 friendly)
 *
 * Use directly in Filament:
 * - Select::make('type')->options(CommissionTypeCast::class)
 * - TextEntry::make('type')->badge()
 *
 * And in Eloquent:
 * - protected $casts = ['type' => CommissionTypeCast::class];
 */
enum CommissionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    // Member (Affiliate tree)
    case SPONSOR_BONUS = 'sponsor_bonus';
    case LEVEL_COMMISSION = 'level_commission';
    case MATCHING_BONUS = 'matching_bonus';
    case LEVEL_ACHIEVEMENT = 'level_achievement';
    case POOL_BONUS = 'pool_bonus';
    case PURCHASE_COMMISSION = 'purchase_commission';
    case RENEWAL_BONUS = 'renewal_bonus';

    // Administrative
    case ADJUSTMENT = 'adjustment';
    case REVERSAL = 'reversal';

    // Originator
    case ORIGINATOR_JOINING = 'originator_joining';
    case ORIGINATOR_RECURRING = 'originator_recurring';
    case AGENT_SALARY = 'agent_salary';
    case INCOME_DEDUCTION = 'income_deduction';

    // Task/Activity based
    case TASK_COMPLETION = 'task_completion';
    case MILESTONE_BONUS = 'milestone_bonus';
    case REFERRAL_BONUS = 'referral_bonus';
    case PERFORMANCE_BONUS = 'performance_bonus';
    case CUSTOM = 'custom';

    /* -------------------------------------------------
     | Filament UI
     -------------------------------------------------*/

    public function getLabel(): string
    {
        return match ($this) {
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
        };
    }

    public function getColor(): string|array|null
    {
        // Idea: positive earning = green-ish, deductions/reversals = red,
        // admin/manual = amber, task/activity = blue-ish, originator = purple-ish.
        return match ($this) {
            // Positive member commissions
            self::SPONSOR_BONUS,
            self::LEVEL_COMMISSION,
            self::MATCHING_BONUS,
            self::LEVEL_ACHIEVEMENT,
            self::POOL_BONUS,
            self::PURCHASE_COMMISSION,
            self::RENEWAL_BONUS => 'success',

            // Admin/manual actions
            self::ADJUSTMENT => Color::Amber,

            // Deductions
            self::REVERSAL,
            self::INCOME_DEDUCTION => 'danger',

            // Originator group
            self::ORIGINATOR_JOINING,
            self::ORIGINATOR_RECURRING,
            self::AGENT_SALARY => Color::Purple,

            // Task/activity group
            self::TASK_COMPLETION,
            self::MILESTONE_BONUS,
            self::REFERRAL_BONUS,
            self::PERFORMANCE_BONUS => Color::Blue,

            self::CUSTOM => Color::Gray,
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SPONSOR_BONUS => 'heroicon-o-user-plus',
            self::LEVEL_COMMISSION => 'heroicon-o-bars-3-bottom-left',
            self::MATCHING_BONUS => 'heroicon-o-squares-2x2',
            self::LEVEL_ACHIEVEMENT => 'heroicon-o-trophy',
            self::POOL_BONUS => 'heroicon-o-circle-stack',
            self::PURCHASE_COMMISSION => 'heroicon-o-shopping-cart',
            self::RENEWAL_BONUS => 'heroicon-o-arrow-path',

            self::ADJUSTMENT => 'heroicon-o-adjustments-horizontal',
            self::REVERSAL => 'heroicon-o-arrow-uturn-left',

            self::ORIGINATOR_JOINING => 'heroicon-o-user-circle',
            self::ORIGINATOR_RECURRING => 'heroicon-o-arrow-path-rounded-square',
            self::AGENT_SALARY => 'heroicon-o-banknotes',
            self::INCOME_DEDUCTION => 'heroicon-o-minus-circle',

            self::TASK_COMPLETION => 'heroicon-o-check-badge',
            self::MILESTONE_BONUS => 'heroicon-o-flag',
            self::REFERRAL_BONUS => 'heroicon-o-link',
            self::PERFORMANCE_BONUS => 'heroicon-o-chart-bar-square',

            self::CUSTOM => 'heroicon-o-wrench-screwdriver',
        };
    }

    /* -------------------------------------------------
     | Groups / helpers (same capability as your old class)
     -------------------------------------------------*/

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

    public static function originatorTypes(): array
    {
        return [
            self::ORIGINATOR_JOINING,
            self::ORIGINATOR_RECURRING,
            self::AGENT_SALARY,
        ];
    }

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

    public static function deductionTypes(): array
    {
        return [
            self::INCOME_DEDUCTION,
            self::REVERSAL,
        ];
    }

    public static function configKey(self|string $type): ?string
    {
        $value = $type instanceof self ? $type->value : $type;

        return match ($value) {
            self::SPONSOR_BONUS->value => 'affiliate.member_commissions.sponsor_bonus.enabled',
            self::LEVEL_COMMISSION->value => 'affiliate.member_commissions.level_commission.enabled',
            self::MATCHING_BONUS->value => 'affiliate.member_commissions.matching_bonus.enabled',
            self::LEVEL_ACHIEVEMENT->value => 'affiliate.member_commissions.level_achievement.enabled',
            self::POOL_BONUS->value => 'affiliate.member_commissions.pool_bonus.enabled',
            self::PURCHASE_COMMISSION->value => 'affiliate.member_commissions.purchase_commission.enabled',
            self::RENEWAL_BONUS->value => 'affiliate.member_commissions.renewal_bonus.enabled',
            self::ORIGINATOR_JOINING->value => 'affiliate.originator_commissions.joining_commission.enabled',
            self::ORIGINATOR_RECURRING->value => 'affiliate.originator_commissions.recurring_commission.enabled',
            self::AGENT_SALARY->value => 'affiliate.agent_salary.enabled',
            self::INCOME_DEDUCTION->value => 'affiliate.income_deduction.enabled',
            self::TASK_COMPLETION->value => 'affiliate.task_commissions.task_completion.enabled',
            self::MILESTONE_BONUS->value => 'affiliate.task_commissions.milestone_bonus.enabled',
            self::REFERRAL_BONUS->value => 'affiliate.task_commissions.referral_bonus.enabled',
            self::PERFORMANCE_BONUS->value => 'affiliate.task_commissions.performance_bonus.enabled',
            self::CUSTOM->value => 'affiliate.task_commissions.custom.enabled',
            default => null,
        };
    }

    public static function isEnabled(self|string $type): bool
    {
        $key = self::configKey($type);

        if ($key === null) {
            return true;
        }

        return (bool) config($key, false);
    }

    public function isPositive(): bool
    {
        return ! in_array($this, [self::REVERSAL, self::INCOME_DEDUCTION], true);
    }

    public function requiresApproval(): bool
    {
        return in_array($this, [self::ADJUSTMENT, self::POOL_BONUS], true);
    }

    public function isOneTime(): bool
    {
        return in_array($this, [self::SPONSOR_BONUS, self::LEVEL_ACHIEVEMENT], true);
    }
}

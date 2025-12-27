<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Condition Matching Type for Sales and Vouchers
 *
 * Determines how multiple conditions are evaluated
 */
enum ConditionMatchingCast: string implements HasColor, HasIcon, HasLabel
{
    case MATCH_ALL = 'match_all';   // AND logic - all conditions must be true
    case MATCH_ANY = 'match_any';   // OR logic - any condition can be true

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MATCH_ALL => 'success',
            self::MATCH_ANY => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::MATCH_ALL => 'heroicon-o-check-circle',
            self::MATCH_ANY => 'heroicon-o-adjustments-horizontal',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MATCH_ALL => 'Match All Conditions (AND)',
            self::MATCH_ANY => 'Match Any Condition (OR)',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::MATCH_ALL => 'All conditions must be satisfied for the discount to apply',
            self::MATCH_ANY => 'At least one condition must be satisfied for the discount to apply',
        };
    }

    /**
     * Evaluate conditions based on the matching type
     *
     * @param  array<bool>  $results  Array of condition evaluation results
     */
    public function evaluate(array $results): bool
    {
        if (empty($results)) {
            return true; // No conditions = always valid
        }

        return match ($this) {
            self::MATCH_ALL => ! in_array(false, $results, true),
            self::MATCH_ANY => in_array(true, $results, true),
        };
    }

    /**
     * Check if this requires all conditions
     */
    public function requiresAll(): bool
    {
        return $this === self::MATCH_ALL;
    }

    /**
     * Check if this requires any condition
     */
    public function requiresAny(): bool
    {
        return $this === self::MATCH_ANY;
    }
}

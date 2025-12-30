<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

use Illuminate\Database\Eloquent\Model;

/**
 * Contract for events/entities that TRIGGER commission calculations
 *
 * Any entity implementing this can trigger commissions:
 * - UserSubscription (joining)
 * - Order (purchase)
 * - Transaction (withdrawal)
 * - Task completion (future)
 */
interface CommissionTrigger
{
    public function getId(): int;

    /**
     * Get the base amount for commission calculation (in paisa)
     */
    public function getCommissionableAmount(): int;

    /**
     * Get the user who triggered this (whose action generates commissions)
     */
    public function getTriggeringUserId(): int;

    /**
     * Get the trigger type identifier
     */
    public function getTriggerType(): string;

    /**
     * Get the model instance for polymorphic relation
     */
    public function getModel(): Model;

    /**
     * Get additional context data for commission calculation
     */
    public function getCommissionContext(): array;
}

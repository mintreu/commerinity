<?php

declare(strict_types=1);

namespace App\Contracts\Mlm;

/**
 * Contract for entities that can RECEIVE commissions
 *
 * Any entity implementing this can earn commissions:
 * - User (members, promoters)
 * - Staff (future)
 * - Partner (future)
 */
interface CommissionableEntity
{
    public function getId(): int;

    public function getUuid(): string;

    public function getType(): string;

    /**
     * Get genealogy ID if applicable (null for non-MLM entities)
     */
    public function getGenealogyId(): ?int;

    /**
     * Check if entity is eligible to receive commissions
     */
    public function canReceiveCommissions(): bool;

    /**
     * Get commission multiplier (from level, performance, etc.)
     */
    public function getCommissionMultiplier(): float;
}

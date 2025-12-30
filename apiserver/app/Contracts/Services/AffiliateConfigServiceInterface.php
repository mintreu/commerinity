<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface AffiliateConfigServiceInterface
{
    /**
     * Check if member commissions are enabled
     */
    public function isMemberCommissionsEnabled(): bool;

    /**
     * Check if originator commissions are enabled
     */
    public function isOriginatorCommissionsEnabled(): bool;

    /**
     * Check if task commissions are enabled
     */
    public function isTaskCommissionsEnabled(): bool;

    /**
     * Get list of enabled commission types
     *
     * @return array<string>
     */
    public function getEnabledCommissionTypes(): array;

    /**
     * Check if specific commission type is enabled
     */
    public function isCommissionTypeEnabled(string $type): bool;

    /**
     * Calculate TDS (Tax Deducted at Source) amount
     */
    public function calculateTds(int $amountPaisa): int;

    /**
     * Calculate admin fee
     */
    public function calculateAdminFee(int $amountPaisa): int;

    /**
     * Calculate income deduction
     */
    public function calculateIncomeDeduction(int $amountPaisa): int;

    /**
     * Calculate platform fee
     */
    public function calculatePlatformFee(int $amountPaisa): int;

    /**
     * Get configuration summary
     *
     * @return array{
     *   member_commissions_enabled: bool,
     *   originator_commissions_enabled: bool,
     *   task_commissions_enabled: bool,
     *   tds_rate: float,
     *   admin_fee_rate: float,
     *   platform_fee_rate: float,
     *   enabled_types: array
     * }
     */
    public function getConfigSummary(): array;
}

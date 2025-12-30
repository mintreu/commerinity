<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

/**
 * Contract for Affiliate Configuration Service
 *
 * Centralized configuration service for Affiliate feature flags, commission settings,
 * deduction calculations, and visibility controls. All Affiliate components should
 * use this interface for config checks to ensure consistency and testability.
 *
 * Key responsibilities:
 * - Master switches for commission categories
 * - Individual commission type enable/disable
 * - TDS, admin fee, platform fee calculations
 * - Dashboard and invoice visibility settings
 * - Level-based feature unlocks
 *
 * Scalability considerations:
 * - Config values should be cached (Laravel config is already cached)
 * - Heavy calculations should be memoized within request lifecycle
 * - For 1B+ users, consider Redis-backed config with TTL
 */
interface AffiliateConfigServiceInterface
{
    // ========================================
    // MASTER SWITCHES
    // ========================================

    /**
     * Check if member commissions (level, sponsor bonus) are enabled
     */
    public function isMemberCommissionsEnabled(): bool;

    /**
     * Check if originator commissions are enabled
     */
    public function isOriginatorCommissionsEnabled(): bool;

    /**
     * Check if task-based commissions are enabled
     */
    public function isTaskCommissionsEnabled(): bool;

    /**
     * Check if income deduction is enabled
     */
    public function isIncomeDeductionEnabled(): bool;

    /**
     * Check if agent salary system is enabled
     */
    public function isAgentSalaryEnabled(): bool;

    /**
     * Check if level-based features are enabled
     */
    public function isLevelFeaturesEnabled(): bool;

    // ========================================
    // COMMISSION TYPE CHECKS
    // ========================================

    /**
     * Check if a specific commission type is enabled
     *
     * Checks both master switch and individual type config.
     */
    public function isCommissionTypeEnabled(string $type): bool;

    /**
     * Get all enabled commission types
     *
     * @return array<int, string>
     */
    public function getEnabledCommissionTypes(): array;

    /**
     * Get enabled member commission types
     *
     * @return array<int, string>
     */
    public function getEnabledMemberTypes(): array;

    /**
     * Get enabled originator commission types
     *
     * @return array<int, string>
     */
    public function getEnabledOriginatorTypes(): array;

    /**
     * Get enabled task commission types
     *
     * @return array<int, string>
     */
    public function getEnabledTaskTypes(): array;

    // ========================================
    // ORIGINATOR COMMISSION CONFIG
    // ========================================

    /**
     * Get originator joining commission config
     *
     * @return array{enabled: bool, type: string, value: float}
     */
    public function getOriginatorJoiningConfig(): array;

    /**
     * Get originator recurring commission config
     *
     * @return array{enabled: bool, type: string, value: float, frequency: string}
     */
    public function getOriginatorRecurringConfig(): array;

    // ========================================
    // DEDUCTION CALCULATIONS
    // ========================================

    /**
     * Check if TDS is enabled
     */
    public function isTdsEnabled(): bool;

    /**
     * Get TDS configuration
     *
     * @return array{enabled: bool, threshold_monthly: int, rate_percent: float}
     */
    public function getTdsConfig(): array;

    /**
     * Calculate TDS amount based on commission and monthly total
     *
     * @param  int  $amount  Commission amount in paisa
     * @param  int  $monthlyTotal  User's monthly total in paisa
     * @return int TDS amount in paisa
     */
    public function calculateTds(int $amount, int $monthlyTotal): int;

    /**
     * Check if admin fee is enabled
     */
    public function isAdminFeeEnabled(): bool;

    /**
     * Get admin fee configuration
     *
     * @return array{enabled: bool, type: string, value: float, description: string}
     */
    public function getAdminFeeConfig(): array;

    /**
     * Calculate admin fee amount
     *
     * @param  int  $amount  Commission amount in paisa
     * @return int Admin fee in paisa
     */
    public function calculateAdminFee(int $amount): int;

    /**
     * Get income deduction configuration
     *
     * @return array{enabled: bool, percent: float, description: string, show_on_invoice: bool}
     */
    public function getIncomeDeductionConfig(): array;

    /**
     * Calculate income deduction amount
     *
     * @param  int  $grossAmount  Gross amount in paisa
     * @return int Deduction amount in paisa
     */
    public function calculateIncomeDeduction(int $grossAmount): int;

    // ========================================
    // PLATFORM FEE
    // ========================================

    /**
     * Check if platform fee is enabled
     */
    public function isPlatformFeeEnabled(): bool;

    /**
     * Get platform fee config for a user type
     *
     * @param  string|null  $userType  User type (member, originator, etc.)
     * @return array{enabled: bool, type: string, value: float, triggers: array, min_threshold: int, show_on_invoice: bool, description: string}
     */
    public function getPlatformFeeConfig(?string $userType = null): array;

    /**
     * Check if platform fee should be applied
     */
    public function shouldApplyPlatformFee(string $trigger, string $userType, int $amount): bool;

    /**
     * Calculate platform fee for user and amount
     *
     * @param  int  $amount  Amount in paisa
     * @param  string  $userType  User type
     * @param  string  $trigger  Trigger event (withdrawal, commission, monthly)
     * @return int Platform fee in paisa
     */
    public function calculatePlatformFee(int $amount, string $userType, string $trigger = 'commission'): int;

    /**
     * Get platform fee summary
     *
     * @return array<string, mixed>
     */
    public function getPlatformFeeSummary(): array;

    // ========================================
    // AGENT SALARY
    // ========================================

    /**
     * Get agent salary tiers
     *
     * @return array<int, array{name: string, min_originated_users: int, min_team_sales: int, salary: int}>
     */
    public function getAgentSalaryTiers(): array;

    /**
     * Get salary tier for given metrics
     *
     * @return array{name: string, min_originated_users: int, min_team_sales: int, salary: int}|null
     */
    public function getSalaryTierForMetrics(int $originatedUsers, int $teamSales): ?array;

    // ========================================
    // VISIBILITY CONFIG
    // ========================================

    /**
     * Get dashboard visibility settings for a user type
     *
     * @return array<string, bool>
     */
    public function getDashboardVisibility(string $userType): array;

    /**
     * Check if a dashboard element should be shown
     */
    public function shouldShowDashboardElement(string $userType, string $element): bool;

    /**
     * Get invoice visibility settings
     *
     * @return array<string, bool>
     */
    public function getInvoiceVisibility(): array;

    /**
     * Check if element should be shown on invoice
     */
    public function shouldShowOnInvoice(string $element): bool;

    // ========================================
    // LEVEL FEATURES
    // ========================================

    /**
     * Get features unlocked at a global rank
     *
     * @return array<int, string>
     */
    public function getFeaturesAtRank(int $globalRank): array;

    /**
     * Check if a feature is unlocked for a rank
     */
    public function isFeatureUnlocked(string $feature, int $globalRank): bool;

    // ========================================
    // SUMMARY
    // ========================================

    /**
     * Get full configuration summary
     *
     * @return array<string, mixed>
     */
    public function getConfigSummary(): array;
}

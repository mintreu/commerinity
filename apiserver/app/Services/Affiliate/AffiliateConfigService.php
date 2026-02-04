<?php

declare(strict_types=1);

namespace App\Services\Affiliate;

use App\Casts\CommissionTypeCast;

/**
 * Affiliate Configuration Service
 *
 * Centralized service for checking Affiliate feature flags and configuration.
 * All commission enable/disable checks should go through this service.
 */
final class AffiliateConfigService
{
    // ========================================
    // MASTER SWITCHES
    // ========================================

    public function isMemberCommissionsEnabled(): bool
    {
        return (bool) config('affiliate.member_commissions.enabled', true);
    }

    public function isOriginatorCommissionsEnabled(): bool
    {
        return (bool) config('affiliate.originator_commissions.enabled', true);
    }

    public function isTaskCommissionsEnabled(): bool
    {
        return (bool) config('affiliate.task_commissions.enabled', true);
    }

    public function isIncomeDeductionEnabled(): bool
    {
        return (bool) config('affiliate.income_deduction.enabled', false);
    }

    public function isAgentSalaryEnabled(): bool
    {
        return (bool) config('affiliate.agent_salary.enabled', false);
    }

    public function isLevelFeaturesEnabled(): bool
    {
        return (bool) config('affiliate.level_features.enabled', true);
    }

    // ========================================
    // COMMISSION TYPE CHECKS
    // ========================================

    public function isCommissionTypeEnabled(CommissionTypeCast|string $type): bool
    {
        $typeValue = $type instanceof CommissionTypeCast ? $type->value : $type;

        // Check master switch first
        if (in_array($typeValue, CommissionTypeCast::memberTypes(), true)) {
            if (! $this->isMemberCommissionsEnabled()) {
                return false;
            }
        }

        if (in_array($typeValue, CommissionTypeCast::originatorTypes(), true)) {
            if (! $this->isOriginatorCommissionsEnabled()) {
                return false;
            }
        }

        if (in_array($typeValue, CommissionTypeCast::taskTypes(), true)) {
            if (! $this->isTaskCommissionsEnabled()) {
                return false;
            }
        }

        // Check specific type config
        return CommissionTypeCast::isEnabled($typeValue);
    }

    /**
     * Get all enabled commission types
     */
    public function getEnabledCommissionTypes(): array
    {
        return array_filter(
            CommissionTypeCast::values(),
            fn (string $type) => $this->isCommissionTypeEnabled($type)
        );
    }

    /**
     * Get enabled types for a specific category
     */
    public function getEnabledMemberTypes(): array
    {
        if (! $this->isMemberCommissionsEnabled()) {
            return [];
        }

        return array_filter(
            CommissionTypeCast::memberTypes(),
            fn (string $type) => CommissionTypeCast::isEnabled($type)
        );
    }

    public function getEnabledOriginatorTypes(): array
    {
        if (! $this->isOriginatorCommissionsEnabled()) {
            return [];
        }

        return array_filter(
            CommissionTypeCast::originatorTypes(),
            fn (string $type) => CommissionTypeCast::isEnabled($type)
        );
    }

    public function getEnabledTaskTypes(): array
    {
        if (! $this->isTaskCommissionsEnabled()) {
            return [];
        }

        return array_filter(
            CommissionTypeCast::taskTypes(),
            fn (string $type) => CommissionTypeCast::isEnabled($type)
        );
    }

    // ========================================
    // ORIGINATOR COMMISSION CONFIG
    // ========================================

    public function getOriginatorJoiningConfig(): array
    {
        return [
            'enabled' => $this->isCommissionTypeEnabled(CommissionTypeCast::ORIGINATOR_JOINING),
            'type' => config('affiliate.originator_commissions.joining_commission.type', 'percent'),
            'value' => (float) config('affiliate.originator_commissions.joining_commission.value', 5),
        ];
    }

    public function getOriginatorRecurringConfig(): array
    {
        return [
            'enabled' => $this->isCommissionTypeEnabled(CommissionTypeCast::ORIGINATOR_RECURRING),
            'type' => config('affiliate.originator_commissions.recurring_commission.type', 'percent'),
            'value' => (float) config('affiliate.originator_commissions.recurring_commission.value', 2),
            'frequency' => config('affiliate.originator_commissions.recurring_commission.frequency', 'on_withdrawal'),
        ];
    }

    // ========================================
    // INCOME DEDUCTION CONFIG
    // ========================================

    public function getIncomeDeductionConfig(): array
    {
        return [
            'enabled' => $this->isIncomeDeductionEnabled(),
            'percent' => (float) config('affiliate.income_deduction.percent', 3),
            'description' => config('affiliate.income_deduction.description', 'Platform Service Fee'),
            'show_on_invoice' => (bool) config('affiliate.income_deduction.show_on_invoice', true),
        ];
    }

    /**
     * Calculate income deduction amount
     */
    public function calculateIncomeDeduction(int $grossAmount): int
    {
        if (! $this->isIncomeDeductionEnabled()) {
            return 0;
        }

        $percent = (float) config('affiliate.income_deduction.percent', 3);

        return (int) round($grossAmount * ($percent / 100));
    }

    // ========================================
    // AGENT SALARY CONFIG
    // ========================================

    public function getAgentSalaryTiers(): array
    {
        if (! $this->isAgentSalaryEnabled()) {
            return [];
        }

        return config('affiliate.agent_salary.tiers', []);
    }

    /**
     * Get salary tier for given metrics
     */
    public function getSalaryTierForMetrics(int $originatedUsers, int $teamSales): ?array
    {
        $tiers = $this->getAgentSalaryTiers();

        // Sort by requirements descending to get highest matching tier
        usort($tiers, fn ($a, $b) => $b['min_originated_users'] <=> $a['min_originated_users']);

        foreach ($tiers as $tier) {
            if ($originatedUsers >= $tier['min_originated_users']
                && $teamSales >= $tier['min_team_sales']) {
                return $tier;
            }
        }

        return null;
    }

    // ========================================
    // TDS CONFIG
    // ========================================

    public function isTdsEnabled(): bool
    {
        return (bool) config('affiliate.tds.enabled', true);
    }

    public function getTdsConfig(): array
    {
        return [
            'enabled' => $this->isTdsEnabled(),
            'threshold_monthly' => (int) config('affiliate.tds.threshold_monthly', 500000),
            'rate_percent' => (float) config('affiliate.tds.rate_percent', 10),
        ];
    }

    /**
     * Calculate TDS amount
     */
    public function calculateTds(int $amount, int $monthlyTotal): int
    {
        if (! $this->isTdsEnabled()) {
            return 0;
        }

        $threshold = (int) config('affiliate.tds.threshold_monthly', 500000);
        $rate = (float) config('affiliate.tds.rate_percent', 10);

        if (($monthlyTotal + $amount) <= $threshold) {
            return 0;
        }

        return (int) round($amount * ($rate / 100));
    }

    // ========================================
    // ADMIN FEE CONFIG
    // ========================================

    public function isAdminFeeEnabled(): bool
    {
        return (bool) config('affiliate.admin_fee.enabled', false);
    }

    public function getAdminFeeConfig(): array
    {
        return [
            'enabled' => $this->isAdminFeeEnabled(),
            'type' => config('affiliate.admin_fee.type', 'percent'),
            'value' => (float) config('affiliate.admin_fee.value', 0),
            'description' => config('affiliate.admin_fee.description', 'Admin Fee'),
        ];
    }

    /**
     * Calculate admin fee amount (legacy - prefer calculatePlatformFee)
     */
    public function calculateAdminFee(int $amount): int
    {
        if (! $this->isAdminFeeEnabled()) {
            return 0;
        }

        $type = config('affiliate.admin_fee.type', 'percent');
        $value = (float) config('affiliate.admin_fee.value', 0);

        if ($type === 'percent') {
            return (int) round($amount * ($value / 100));
        }

        // Fixed amount
        return (int) $value;
    }

    // ========================================
    // PLATFORM FEE CONFIG (Comprehensive)
    // ========================================

    public function isPlatformFeeEnabled(): bool
    {
        return (bool) config('affiliate.platform_fee.enabled', false);
    }

    /**
     * Get platform fee config for a specific user type
     */
    public function getPlatformFeeConfig(?string $userType = null): array
    {
        $default = [
            'enabled' => $this->isPlatformFeeEnabled(),
            'type' => config('affiliate.platform_fee.default.type', 'percent'),
            'value' => (float) config('affiliate.platform_fee.default.value', 2),
            'triggers' => config('affiliate.platform_fee.triggers', []),
            'min_threshold' => (int) config('affiliate.platform_fee.min_amount_threshold', 10000),
            'show_on_invoice' => (bool) config('affiliate.platform_fee.show_on_invoice', true),
            'description' => config('affiliate.platform_fee.description', 'Platform Service Fee'),
        ];

        if (! $userType) {
            return $default;
        }

        // Check if user type is excluded
        $excludedTypes = config('affiliate.platform_fee.excluded_types', []);
        if (in_array($userType, $excludedTypes, true)) {
            return array_merge($default, ['enabled' => false, 'excluded' => true]);
        }

        // Get user type specific config
        $userConfig = config("affiliate.platform_fee.user_types.{$userType}", []);

        if (empty($userConfig) || ! ($userConfig['enabled'] ?? true)) {
            return array_merge($default, ['enabled' => false]);
        }

        // Merge with default (user config overrides)
        return [
            'enabled' => $this->isPlatformFeeEnabled() && ($userConfig['enabled'] ?? true),
            'type' => $userConfig['type'] ?? $default['type'],
            'value' => (float) ($userConfig['value'] ?? $default['value']),
            'triggers' => $default['triggers'],
            'min_threshold' => $default['min_threshold'],
            'show_on_invoice' => $default['show_on_invoice'],
            'description' => $default['description'],
        ];
    }

    /**
     * Check if platform fee should be applied for a trigger event
     */
    public function shouldApplyPlatformFee(string $trigger, string $userType, int $amount): bool
    {
        if (! $this->isPlatformFeeEnabled()) {
            return false;
        }

        $config = $this->getPlatformFeeConfig($userType);

        if (! $config['enabled']) {
            return false;
        }

        // Check if trigger is enabled
        $triggerKey = match ($trigger) {
            'withdrawal' => 'on_withdrawal',
            'commission' => 'on_commission',
            'monthly' => 'monthly',
            default => null,
        };

        if (! $triggerKey || ! ($config['triggers'][$triggerKey] ?? false)) {
            return false;
        }

        // Check minimum threshold
        if ($amount < $config['min_threshold']) {
            return false;
        }

        return true;
    }

    /**
     * Calculate platform fee for a user and amount
     */
    public function calculatePlatformFee(int $amount, string $userType, string $trigger = 'commission'): int
    {
        if (! $this->shouldApplyPlatformFee($trigger, $userType, $amount)) {
            return 0;
        }

        $config = $this->getPlatformFeeConfig($userType);
        $type = $config['type'];
        $value = $config['value'];

        if ($type === 'percent') {
            return (int) round($amount * ($value / 100));
        }

        // Fixed amount
        return (int) $value;
    }

    /**
     * Get all platform fee settings summary
     */
    public function getPlatformFeeSummary(): array
    {
        return [
            'enabled' => $this->isPlatformFeeEnabled(),
            'default' => config('affiliate.platform_fee.default', []),
            'triggers' => config('affiliate.platform_fee.triggers', []),
            'user_types' => collect(config('affiliate.platform_fee.user_types', []))
                ->map(fn ($config, $type) => $this->getPlatformFeeConfig($type))
                ->toArray(),
            'excluded_types' => config('affiliate.platform_fee.excluded_types', []),
            'min_threshold' => (int) config('affiliate.platform_fee.min_amount_threshold', 0),
        ];
    }

    // ========================================
    // DASHBOARD VISIBILITY
    // ========================================

    /**
     * Get dashboard visibility settings for a user type
     */
    public function getDashboardVisibility(string $userType): array
    {
        $config = config("affiliate.dashboard_visibility.{$userType}", []);

        return array_map(
            fn ($value) => $this->resolveConfigValue($value),
            $config
        );
    }

    /**
     * Check if a specific dashboard element should be shown
     */
    public function shouldShowDashboardElement(string $userType, string $element): bool
    {
        $visibility = $this->getDashboardVisibility($userType);

        return (bool) ($visibility[$element] ?? false);
    }

    // ========================================
    // INVOICE VISIBILITY
    // ========================================

    public function getInvoiceVisibility(): array
    {
        $config = config('affiliate.invoice', []);

        return array_map(
            fn ($value) => $this->resolveConfigValue($value),
            $config
        );
    }

    public function shouldShowOnInvoice(string $element): bool
    {
        $visibility = $this->getInvoiceVisibility();

        return (bool) ($visibility[$element] ?? false);
    }

    // ========================================
    // LEVEL FEATURES
    // ========================================

    /**
     * Get features unlocked at a specific global rank
     */
    public function getFeaturesAtRank(int $globalRank): array
    {
        if (! $this->isLevelFeaturesEnabled()) {
            return [];
        }

        $unlocks = config('affiliate.level_features.unlocks', []);
        $features = [];

        foreach ($unlocks as $rank => $rankFeatures) {
            if ($rank <= $globalRank) {
                $features = array_merge($features, $rankFeatures);
            }
        }

        return array_unique($features);
    }

    /**
     * Check if a feature is unlocked for a rank
     */
    public function isFeatureUnlocked(string $feature, int $globalRank): bool
    {
        $features = $this->getFeaturesAtRank($globalRank);

        return in_array($feature, $features, true);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Resolve dynamic config values (e.g., 'config:affiliate.income_deduction.enabled')
     */
    private function resolveConfigValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (str_starts_with($value, 'config:')) {
            $configKey = substr($value, 7);

            return (bool) config($configKey, false);
        }

        return $value;
    }

    /**
     * Get full config summary (for debugging/admin panel)
     */
    public function getConfigSummary(): array
    {
        return [
            'member_commissions' => [
                'enabled' => $this->isMemberCommissionsEnabled(),
                'types' => $this->getEnabledMemberTypes(),
            ],
            'originator_commissions' => [
                'enabled' => $this->isOriginatorCommissionsEnabled(),
                'types' => $this->getEnabledOriginatorTypes(),
                'joining' => $this->getOriginatorJoiningConfig(),
                'recurring' => $this->getOriginatorRecurringConfig(),
            ],
            'task_commissions' => [
                'enabled' => $this->isTaskCommissionsEnabled(),
                'types' => $this->getEnabledTaskTypes(),
            ],
            'income_deduction' => $this->getIncomeDeductionConfig(),
            'agent_salary' => [
                'enabled' => $this->isAgentSalaryEnabled(),
                'tiers' => $this->getAgentSalaryTiers(),
            ],
            'tds' => $this->getTdsConfig(),
            'level_features' => [
                'enabled' => $this->isLevelFeaturesEnabled(),
            ],
        ];
    }
}

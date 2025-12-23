<?php

declare(strict_types=1);

use App\Casts\CommissionTypeCast;
use App\Services\Mlm\MlmConfigService;

beforeEach(function () {
    $this->configService = new MlmConfigService;
});

// ========================================
// Master Switch Tests
// ========================================

describe('master switches', function () {
    it('checks member commissions master switch', function () {
        config(['mlm.member_commissions.enabled' => true]);
        expect($this->configService->isMemberCommissionsEnabled())->toBeTrue();

        config(['mlm.member_commissions.enabled' => false]);
        expect($this->configService->isMemberCommissionsEnabled())->toBeFalse();
    });

    it('checks originator commissions master switch', function () {
        config(['mlm.originator_commissions.enabled' => true]);
        expect($this->configService->isOriginatorCommissionsEnabled())->toBeTrue();

        config(['mlm.originator_commissions.enabled' => false]);
        expect($this->configService->isOriginatorCommissionsEnabled())->toBeFalse();
    });

    it('checks task commissions master switch', function () {
        config(['mlm.task_commissions.enabled' => true]);
        expect($this->configService->isTaskCommissionsEnabled())->toBeTrue();

        config(['mlm.task_commissions.enabled' => false]);
        expect($this->configService->isTaskCommissionsEnabled())->toBeFalse();
    });

    it('checks income deduction master switch', function () {
        config(['mlm.income_deduction.enabled' => true]);
        expect($this->configService->isIncomeDeductionEnabled())->toBeTrue();

        config(['mlm.income_deduction.enabled' => false]);
        expect($this->configService->isIncomeDeductionEnabled())->toBeFalse();
    });

    it('checks agent salary master switch', function () {
        config(['mlm.agent_salary.enabled' => true]);
        expect($this->configService->isAgentSalaryEnabled())->toBeTrue();

        config(['mlm.agent_salary.enabled' => false]);
        expect($this->configService->isAgentSalaryEnabled())->toBeFalse();
    });
});

// ========================================
// Commission Type Enable/Disable Tests
// ========================================

describe('commission type checks', function () {
    it('respects member master switch for member types', function () {
        config(['mlm.member_commissions.enabled' => false]);
        config(['mlm.member_commissions.sponsor_bonus.enabled' => true]);

        expect($this->configService->isCommissionTypeEnabled(CommissionTypeCast::SPONSOR_BONUS))
            ->toBeFalse();
    });

    it('respects originator master switch for originator types', function () {
        config(['mlm.originator_commissions.enabled' => false]);
        config(['mlm.originator_commissions.joining_commission.enabled' => true]);

        expect($this->configService->isCommissionTypeEnabled(CommissionTypeCast::ORIGINATOR_JOINING))
            ->toBeFalse();
    });

    it('respects task master switch for task types', function () {
        config(['mlm.task_commissions.enabled' => false]);
        config(['mlm.task_commissions.task_completion.enabled' => true]);

        expect($this->configService->isCommissionTypeEnabled(CommissionTypeCast::TASK_COMPLETION))
            ->toBeFalse();
    });

    it('enables type when master and individual switch are on', function () {
        config(['mlm.member_commissions.enabled' => true]);
        config(['mlm.member_commissions.sponsor_bonus.enabled' => true]);

        expect($this->configService->isCommissionTypeEnabled(CommissionTypeCast::SPONSOR_BONUS))
            ->toBeTrue();
    });

    it('gets enabled member types', function () {
        config(['mlm.member_commissions.enabled' => true]);
        config(['mlm.member_commissions.sponsor_bonus.enabled' => true]);
        config(['mlm.member_commissions.level_commission.enabled' => true]);
        config(['mlm.member_commissions.matching_bonus.enabled' => false]);

        $enabledTypes = $this->configService->getEnabledMemberTypes();

        expect($enabledTypes)->toContain(CommissionTypeCast::SPONSOR_BONUS);
        expect($enabledTypes)->toContain(CommissionTypeCast::LEVEL_COMMISSION);
        expect($enabledTypes)->not->toContain(CommissionTypeCast::MATCHING_BONUS);
    });

    it('returns empty array when member master switch is off', function () {
        config(['mlm.member_commissions.enabled' => false]);

        expect($this->configService->getEnabledMemberTypes())->toBeEmpty();
    });
});

// ========================================
// Originator Commission Config Tests
// ========================================

describe('originator commission config', function () {
    it('gets joining commission config', function () {
        config([
            'mlm.originator_commissions.enabled' => true,
            'mlm.originator_commissions.joining_commission.enabled' => true,
            'mlm.originator_commissions.joining_commission.type' => 'percent',
            'mlm.originator_commissions.joining_commission.value' => 5,
        ]);

        $config = $this->configService->getOriginatorJoiningConfig();

        expect($config['enabled'])->toBeTrue();
        expect($config['type'])->toBe('percent');
        expect($config['value'])->toBe(5.0);
    });

    it('gets recurring commission config', function () {
        config([
            'mlm.originator_commissions.enabled' => true,
            'mlm.originator_commissions.recurring_commission.enabled' => true,
            'mlm.originator_commissions.recurring_commission.type' => 'percent',
            'mlm.originator_commissions.recurring_commission.value' => 2,
            'mlm.originator_commissions.recurring_commission.frequency' => 'on_withdrawal',
        ]);

        $config = $this->configService->getOriginatorRecurringConfig();

        expect($config['enabled'])->toBeTrue();
        expect($config['type'])->toBe('percent');
        expect($config['value'])->toBe(2.0);
        expect($config['frequency'])->toBe('on_withdrawal');
    });
});

// ========================================
// Income Deduction Tests
// ========================================

describe('income deduction', function () {
    it('gets income deduction config', function () {
        config([
            'mlm.income_deduction.enabled' => true,
            'mlm.income_deduction.percent' => 3,
            'mlm.income_deduction.description' => 'Platform Fee',
            'mlm.income_deduction.show_on_invoice' => true,
        ]);

        $config = $this->configService->getIncomeDeductionConfig();

        expect($config['enabled'])->toBeTrue();
        expect($config['percent'])->toBe(3.0);
        expect($config['description'])->toBe('Platform Fee');
        expect($config['show_on_invoice'])->toBeTrue();
    });

    it('calculates income deduction when enabled', function () {
        config([
            'mlm.income_deduction.enabled' => true,
            'mlm.income_deduction.percent' => 3,
        ]);

        $deduction = $this->configService->calculateIncomeDeduction(100000); // ₹1000

        expect($deduction)->toBe(3000); // 3% = ₹30
    });

    it('returns zero deduction when disabled', function () {
        config(['mlm.income_deduction.enabled' => false]);

        $deduction = $this->configService->calculateIncomeDeduction(100000);

        expect($deduction)->toBe(0);
    });
});

// ========================================
// Agent Salary Tests
// ========================================

describe('agent salary', function () {
    it('gets salary tiers when enabled', function () {
        config([
            'mlm.agent_salary.enabled' => true,
            'mlm.agent_salary.tiers' => [
                ['name' => 'Bronze', 'min_originated_users' => 5, 'min_team_sales' => 5000000, 'base_salary' => 500000],
            ],
        ]);

        $tiers = $this->configService->getAgentSalaryTiers();

        expect($tiers)->toHaveCount(1);
        expect($tiers[0]['name'])->toBe('Bronze');
    });

    it('returns empty tiers when disabled', function () {
        config(['mlm.agent_salary.enabled' => false]);

        expect($this->configService->getAgentSalaryTiers())->toBeEmpty();
    });

    it('finds correct salary tier for metrics', function () {
        config([
            'mlm.agent_salary.enabled' => true,
            'mlm.agent_salary.tiers' => [
                ['name' => 'Bronze', 'min_originated_users' => 5, 'min_team_sales' => 5000000, 'base_salary' => 500000],
                ['name' => 'Silver', 'min_originated_users' => 15, 'min_team_sales' => 15000000, 'base_salary' => 1500000],
                ['name' => 'Gold', 'min_originated_users' => 30, 'min_team_sales' => 50000000, 'base_salary' => 3500000],
            ],
        ]);

        // Should match Silver tier
        $tier = $this->configService->getSalaryTierForMetrics(20, 20000000);

        expect($tier)->not->toBeNull();
        expect($tier['name'])->toBe('Silver');
    });

    it('returns null when no tier matches', function () {
        config([
            'mlm.agent_salary.enabled' => true,
            'mlm.agent_salary.tiers' => [
                ['name' => 'Bronze', 'min_originated_users' => 5, 'min_team_sales' => 5000000, 'base_salary' => 500000],
            ],
        ]);

        $tier = $this->configService->getSalaryTierForMetrics(2, 1000000);

        expect($tier)->toBeNull();
    });
});

// ========================================
// TDS Tests
// ========================================

describe('tds calculation', function () {
    it('calculates TDS when threshold exceeded', function () {
        config([
            'mlm.tds.enabled' => true,
            'mlm.tds.threshold_monthly' => 500000, // ₹5000
            'mlm.tds.rate_percent' => 10,
        ]);

        // Monthly total = 400000, new amount = 200000, total = 600000 (exceeds threshold)
        $tds = $this->configService->calculateTds(200000, 400000);

        expect($tds)->toBe(20000); // 10% of 200000
    });

    it('returns zero TDS below threshold', function () {
        config([
            'mlm.tds.enabled' => true,
            'mlm.tds.threshold_monthly' => 500000,
            'mlm.tds.rate_percent' => 10,
        ]);

        // Monthly total = 100000, new amount = 100000, total = 200000 (below threshold)
        $tds = $this->configService->calculateTds(100000, 100000);

        expect($tds)->toBe(0);
    });

    it('returns zero TDS when disabled', function () {
        config(['mlm.tds.enabled' => false]);

        $tds = $this->configService->calculateTds(1000000, 1000000);

        expect($tds)->toBe(0);
    });
});

// ========================================
// Level Features Tests
// ========================================

describe('level features', function () {
    it('gets features at rank when enabled', function () {
        config([
            'mlm.level_features.enabled' => true,
            'mlm.level_features.unlocks' => [
                1 => ['feature_a', 'feature_b'],
                2 => ['feature_c'],
                3 => ['feature_d'],
            ],
        ]);

        $features = $this->configService->getFeaturesAtRank(2);

        expect($features)->toContain('feature_a');
        expect($features)->toContain('feature_b');
        expect($features)->toContain('feature_c');
        expect($features)->not->toContain('feature_d');
    });

    it('returns empty features when disabled', function () {
        config(['mlm.level_features.enabled' => false]);

        expect($this->configService->getFeaturesAtRank(5))->toBeEmpty();
    });

    it('checks if feature is unlocked', function () {
        config([
            'mlm.level_features.enabled' => true,
            'mlm.level_features.unlocks' => [
                1 => ['basic_dashboard'],
                3 => ['advanced_reports'],
            ],
        ]);

        expect($this->configService->isFeatureUnlocked('basic_dashboard', 1))->toBeTrue();
        expect($this->configService->isFeatureUnlocked('advanced_reports', 2))->toBeFalse();
        expect($this->configService->isFeatureUnlocked('advanced_reports', 3))->toBeTrue();
    });
});

// ========================================
// Dashboard Visibility Tests
// ========================================

describe('dashboard visibility', function () {
    it('resolves dynamic config values', function () {
        config([
            'mlm.income_deduction.enabled' => true,
            'mlm.dashboard_visibility.member' => [
                'show_mlm_earnings' => true,
                'show_deduction_info' => 'config:mlm.income_deduction.enabled',
            ],
        ]);

        $visibility = $this->configService->getDashboardVisibility('member');

        expect($visibility['show_mlm_earnings'])->toBeTrue();
        expect($visibility['show_deduction_info'])->toBeTrue();
    });

    it('checks specific dashboard element', function () {
        config([
            'mlm.dashboard_visibility.advisor' => [
                'show_originated_users' => true,
                'show_salary_progress' => false,
            ],
        ]);

        expect($this->configService->shouldShowDashboardElement('advisor', 'show_originated_users'))->toBeTrue();
        expect($this->configService->shouldShowDashboardElement('advisor', 'show_salary_progress'))->toBeFalse();
    });
});

// ========================================
// Config Summary Tests
// ========================================

describe('config summary', function () {
    it('generates complete config summary', function () {
        $summary = $this->configService->getConfigSummary();

        expect($summary)->toHaveKeys([
            'member_commissions',
            'originator_commissions',
            'task_commissions',
            'income_deduction',
            'agent_salary',
            'tds',
            'level_features',
        ]);

        expect($summary['member_commissions'])->toHaveKeys(['enabled', 'types']);
        expect($summary['originator_commissions'])->toHaveKeys(['enabled', 'types', 'joining', 'recurring']);
    });
});

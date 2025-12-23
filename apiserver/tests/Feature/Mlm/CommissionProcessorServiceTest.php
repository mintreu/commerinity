<?php

declare(strict_types=1);

use App\Casts\CommissionTypeCast;
use App\Contracts\Mlm\CommissionCalculator;
use App\Contracts\Mlm\CommissionTrigger;
use App\Dto\Mlm\CommissionResult;
use App\Events\Mlm\CommissionProcessed;
use App\Events\Mlm\CommissionsCalculated;
use App\Events\Mlm\CommissionTriggered;
use App\Models\Mlm\MlmCommission;
use App\Models\User;
use App\Services\Mlm\CommissionProcessorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

// ========================================
// TEST FIXTURES
// ========================================

beforeEach(function () {
    // Set up base config
    Config::set('mlm.member_commissions.enabled', true);
    Config::set('mlm.member_commissions.sponsor_bonus.enabled', true);
    Config::set('mlm.member_commissions.level_commission.enabled', true);
    Config::set('mlm.originator_commissions.enabled', true);
    Config::set('mlm.tds.enabled', false);
    Config::set('mlm.admin_fee.enabled', false);
});

/**
 * Create a mock commission trigger for testing
 */
function createMockTrigger(
    int $userId,
    int $amount,
    string $type = 'subscription',
    ?int $stageId = null,
): CommissionTrigger {
    return new class($userId, $amount, $type, $stageId) implements CommissionTrigger
    {
        private int $id;

        public function __construct(
            private readonly int $userId,
            private readonly int $amount,
            private readonly string $type,
            private readonly ?int $stageId,
        ) {
            $this->id = random_int(1, 10000);
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getCommissionableAmount(): int
        {
            return $this->amount;
        }

        public function getTriggeringUserId(): int
        {
            return $this->userId;
        }

        public function getTriggerType(): string
        {
            return $this->type;
        }

        public function getModel(): Model
        {
            return User::factory()->make(['id' => $this->id]);
        }

        public function getCommissionContext(): array
        {
            return [
                'stage_id' => $this->stageId,
                'test' => true,
            ];
        }
    };
}

/**
 * Create a custom test calculator
 */
function createTestCalculator(
    string $type,
    bool $enabled = true,
    int $priority = 50,
    ?\Closure $calculateFn = null,
): CommissionCalculator {
    return new class($type, $enabled, $priority, $calculateFn) implements CommissionCalculator
    {
        private ?\Closure $calculateFn;

        public function __construct(
            private readonly string $type,
            private readonly bool $enabled,
            private readonly int $priority,
            ?\Closure $calculateFn,
        ) {
            $this->calculateFn = $calculateFn;
        }

        public function getCommissionType(): string
        {
            return $this->type;
        }

        public function supports(CommissionTrigger $trigger): bool
        {
            return true;
        }

        public function isEnabled(): bool
        {
            return $this->enabled;
        }

        public function calculate(CommissionTrigger $trigger): Collection
        {
            if ($this->calculateFn) {
                return ($this->calculateFn)($trigger);
            }

            return collect([
                CommissionResult::bonus(
                    recipientId: $trigger->getTriggeringUserId(),
                    type: $this->type,
                    amount: (int) ($trigger->getCommissionableAmount() * 0.1),
                    description: "Test {$this->type}",
                ),
            ]);
        }

        public function getPriority(): int
        {
            return $this->priority;
        }
    };
}

// ========================================
// SERVICE INSTANTIATION TESTS
// ========================================

describe('CommissionProcessorService instantiation', function () {
    it('creates service with default calculators', function () {
        $service = new CommissionProcessorService;

        $calculators = $service->getCalculators();

        expect($calculators)->toBeArray()
            ->and(count($calculators))->toBeGreaterThan(0);
    });

    it('registers calculators in priority order', function () {
        $service = new CommissionProcessorService;

        // Add custom calculator with highest priority
        $service->register(createTestCalculator('test_high', priority: 200));
        $service->register(createTestCalculator('test_low', priority: 10));

        $calculators = $service->getCalculators();
        $types = array_map(fn ($c) => $c->getCommissionType(), $calculators);

        // Highest priority should be first
        expect($types[0])->toBe('test_high');
    });

    it('filters enabled calculators correctly', function () {
        $service = new CommissionProcessorService;
        $service->register(createTestCalculator('enabled_test', enabled: true));
        $service->register(createTestCalculator('disabled_test', enabled: false));

        $enabledTypes = array_map(
            fn ($c) => $c->getCommissionType(),
            $service->getEnabledCalculators()
        );

        expect($enabledTypes)->toContain('enabled_test')
            ->and($enabledTypes)->not->toContain('disabled_test');
    });
});

// ========================================
// CALCULATION TESTS
// ========================================

describe('Commission calculation', function () {
    it('calculates commissions using all enabled calculators', function () {
        Event::fake();

        $service = new CommissionProcessorService;

        // Clear default calculators using reflection
        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator('type_a', priority: 100));
        $service->register(createTestCalculator('type_b', priority: 50));

        $trigger = createMockTrigger(userId: 1, amount: 100000);
        $results = $service->calculate($trigger);

        expect($results)->toHaveCount(2)
            ->and($results->pluck('type')->toArray())->toContain('type_a', 'type_b');

        Event::assertDispatched(CommissionTriggered::class);
        Event::assertDispatched(CommissionsCalculated::class);
    });

    it('skips disabled calculators', function () {
        $service = new CommissionProcessorService;

        // Replace with controlled calculators
        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator('enabled', enabled: true));
        $service->register(createTestCalculator('disabled', enabled: false));

        Event::fake();
        $trigger = createMockTrigger(userId: 1, amount: 100000);
        $results = $service->calculate($trigger);

        $types = $results->pluck('type')->toArray();
        expect($types)->toContain('enabled')
            ->and($types)->not->toContain('disabled');
    });

    it('handles calculator exceptions gracefully', function () {
        $service = new CommissionProcessorService;

        // Clear and add a throwing calculator
        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $throwingCalc = createTestCalculator(
            'throwing',
            calculateFn: fn () => throw new Exception('Test error')
        );
        $service->register($throwingCalc);
        $service->register(createTestCalculator('working'));

        Event::fake();
        $trigger = createMockTrigger(userId: 1, amount: 100000);

        // Should not throw
        $results = $service->calculate($trigger);

        // Working calculator should still run
        expect($results->pluck('type')->toArray())->toContain('working');
    });
});

// ========================================
// PERSISTENCE TESTS
// ========================================

describe('Commission persistence', function () {
    it('persists commission results to database', function () {
        Event::fake();

        // Create test user
        $user = User::factory()->create();

        $service = new CommissionProcessorService;

        // Replace calculators with simple test one
        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator(
            CommissionTypeCast::SPONSOR_BONUS,
            calculateFn: fn ($trigger) => collect([
                CommissionResult::bonus(
                    recipientId: $user->id,
                    type: CommissionTypeCast::SPONSOR_BONUS,
                    amount: 10000,
                    description: 'Test Sponsor Bonus',
                ),
            ])
        ));

        $trigger = createMockTrigger(userId: $user->id, amount: 100000);
        $commissions = $service->processAndPersist($trigger);

        expect($commissions)->toHaveCount(1);
        expect(MlmCommission::where('user_id', $user->id)->count())->toBe(1);

        $saved = MlmCommission::where('user_id', $user->id)->first();
        expect($saved->type->getValue())->toBe(CommissionTypeCast::SPONSOR_BONUS)
            ->and((int) $saved->gross_amount)->toBe(10000);

        Event::assertDispatched(CommissionProcessed::class);
    });

    it('prevents duplicate commissions', function () {
        $user = User::factory()->create();

        // Create existing commission using factory (which generates UUID properly)
        $existing = MlmCommission::factory()->create([
            'user_id' => $user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'commissionable_type' => User::class,
            'commissionable_id' => 999,
            'gross_amount' => 10000,
            'net_amount' => 10000,
            'tds_amount' => 0,
            'admin_fee' => 0,
            'description' => 'Existing',
        ]);

        // Try to persist duplicate
        $result = new CommissionResult(
            recipientId: $user->id,
            genealogyId: null,
            fromUserId: null,
            type: CommissionTypeCast::SPONSOR_BONUS,
            level: null,
            ratePercent: 10,
            baseAmount: 100000,
            grossAmount: 10000,
            tdsAmount: 0,
            adminFee: 0,
            netAmount: 10000,
            description: 'Duplicate',
            metadata: [],
            commissionableType: User::class,
            commissionableId: 999,
        );

        $service = new CommissionProcessorService;
        Event::fake();
        $commissions = $service->persistResults(collect([$result]));

        // Should not create duplicate
        expect($commissions)->toHaveCount(0);
        expect(MlmCommission::where('user_id', $user->id)->count())->toBe(1);
    });
});

// ========================================
// DEDUCTION TESTS
// ========================================

describe('Commission deductions', function () {
    it('applies TDS when threshold exceeded', function () {
        Config::set('mlm.tds.enabled', true);
        Config::set('mlm.tds.threshold_monthly', 100000); // 1000 INR
        Config::set('mlm.tds.rate_percent', 10);

        $user = User::factory()->create();

        // Create existing commissions to exceed threshold using factory
        MlmCommission::factory()->create([
            'user_id' => $user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'gross_amount' => 200000, // 2000 INR - exceeds threshold
            'net_amount' => 200000,
            'tds_amount' => 0,
            'admin_fee' => 0,
            'description' => 'Previous',
            'created_at' => now(),
        ]);

        $service = new CommissionProcessorService;

        // Replace calculators
        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator(
            CommissionTypeCast::SPONSOR_BONUS,
            calculateFn: fn ($trigger) => collect([
                CommissionResult::bonus(
                    recipientId: $user->id,
                    type: CommissionTypeCast::SPONSOR_BONUS,
                    amount: 50000, // 500 INR
                    description: 'New Commission',
                ),
            ])
        ));

        Event::fake();
        $trigger = createMockTrigger(userId: $user->id, amount: 500000);
        $commissions = $service->processAndPersist($trigger);

        expect($commissions)->toHaveCount(1);
        $commission = $commissions->first();

        // TDS should be applied (10% of 50000 = 5000)
        expect((int) $commission->tds_amount)->toBe(5000)
            ->and((int) $commission->net_amount)->toBe(45000);
    });

    it('does not apply TDS below threshold', function () {
        Config::set('mlm.tds.enabled', true);
        Config::set('mlm.tds.threshold_monthly', 1000000); // 10000 INR
        Config::set('mlm.tds.rate_percent', 10);

        $user = User::factory()->create();

        $service = new CommissionProcessorService;

        // Replace calculators
        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator(
            CommissionTypeCast::SPONSOR_BONUS,
            calculateFn: fn ($trigger) => collect([
                CommissionResult::bonus(
                    recipientId: $user->id,
                    type: CommissionTypeCast::SPONSOR_BONUS,
                    amount: 50000,
                    description: 'Small Commission',
                ),
            ])
        ));

        Event::fake();
        $trigger = createMockTrigger(userId: $user->id, amount: 500000);
        $commissions = $service->processAndPersist($trigger);

        expect($commissions)->toHaveCount(1);
        $commission = $commissions->first();

        // No TDS below threshold
        expect((int) $commission->tds_amount)->toBe(0)
            ->and((int) $commission->net_amount)->toBe(50000);
    });

    it('applies admin fee when enabled', function () {
        Config::set('mlm.admin_fee.enabled', true);
        Config::set('mlm.admin_fee.type', 'percent');
        Config::set('mlm.admin_fee.value', 5);

        $user = User::factory()->create();

        $service = new CommissionProcessorService;

        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator(
            CommissionTypeCast::SPONSOR_BONUS,
            calculateFn: fn ($trigger) => collect([
                CommissionResult::bonus(
                    recipientId: $user->id,
                    type: CommissionTypeCast::SPONSOR_BONUS,
                    amount: 100000, // 1000 INR
                    description: 'Commission with fee',
                ),
            ])
        ));

        Event::fake();
        $trigger = createMockTrigger(userId: $user->id, amount: 1000000);
        $commissions = $service->processAndPersist($trigger);

        expect($commissions)->toHaveCount(1);
        $commission = $commissions->first();

        // Admin fee should be 5% of 100000 = 5000
        expect((int) $commission->admin_fee)->toBe(5000)
            ->and((int) $commission->net_amount)->toBe(95000);
    });
});

// ========================================
// STATISTICS TESTS
// ========================================

describe('Commission statistics', function () {
    it('calculates user statistics correctly', function () {
        $user = User::factory()->create();

        // Create various commissions using factory
        MlmCommission::factory()->create([
            'user_id' => $user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'gross_amount' => 100000,
            'net_amount' => 90000,
            'tds_amount' => 10000,
            'admin_fee' => 0,
            'description' => 'Sponsor Bonus',
            'created_at' => now(),
        ]);

        MlmCommission::factory()->create([
            'user_id' => $user->id,
            'type' => CommissionTypeCast::LEVEL_COMMISSION,
            'gross_amount' => 50000,
            'net_amount' => 45000,
            'tds_amount' => 5000,
            'admin_fee' => 0,
            'description' => 'Level Commission',
            'created_at' => now(),
        ]);

        // Old commission (last month)
        MlmCommission::factory()->create([
            'user_id' => $user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'gross_amount' => 200000,
            'net_amount' => 180000,
            'tds_amount' => 20000,
            'admin_fee' => 0,
            'description' => 'Old Bonus',
            'created_at' => now()->subMonth(),
        ]);

        $service = new CommissionProcessorService;
        $stats = $service->getUserStats($user->id);

        expect($stats['total_earned'])->toBe(350000)
            ->and($stats['total_net'])->toBe(315000)
            ->and($stats['total_tds'])->toBe(35000)
            ->and($stats['commission_count'])->toBe(3)
            ->and($stats['this_month'])->toBe(135000);
    });
});

// ========================================
// SIMULATION TESTS
// ========================================

describe('Commission simulation', function () {
    it('simulates commission calculation without persistence', function () {
        Event::fake();

        $user = User::factory()->create();

        $service = new CommissionProcessorService;

        $reflection = new ReflectionClass($service);
        $prop = $reflection->getProperty('calculators');
        $prop->setAccessible(true);
        $prop->setValue($service, []);

        $service->register(createTestCalculator(
            CommissionTypeCast::SPONSOR_BONUS,
            calculateFn: fn ($trigger) => collect([
                CommissionResult::bonus(
                    recipientId: $user->id,
                    type: CommissionTypeCast::SPONSOR_BONUS,
                    amount: 10000,
                    description: 'Simulated',
                ),
            ])
        ));

        $trigger = createMockTrigger(userId: $user->id, amount: 100000);
        $simulation = $service->simulate($trigger);

        expect($simulation)->toHaveKeys(['trigger', 'results', 'summary'])
            ->and($simulation['summary']['total_commissions'])->toBe(1)
            ->and($simulation['summary']['total_gross'])->toBe(10000);

        // Should NOT persist
        expect(MlmCommission::where('user_id', $user->id)->count())->toBe(0);
    });
});

// ========================================
// CONFIG SUMMARY TESTS
// ========================================

describe('Configuration summary', function () {
    it('returns complete config summary', function () {
        $service = new CommissionProcessorService;
        $summary = $service->getConfigSummary();

        expect($summary)->toHaveKeys([
            'registered_calculators',
            'enabled_calculators',
            'calculators',
            'config',
        ]);

        expect($summary['registered_calculators'])->toBeGreaterThan(0);
    });
});

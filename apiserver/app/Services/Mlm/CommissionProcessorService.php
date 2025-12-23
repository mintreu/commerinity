<?php

declare(strict_types=1);

namespace App\Services\Mlm;

use App\Contracts\Mlm\CommissionCalculator;
use App\Contracts\Mlm\CommissionTrigger;
use App\Dto\Mlm\CommissionResult;
use App\Events\Mlm\CommissionProcessed;
use App\Events\Mlm\CommissionsCalculated;
use App\Events\Mlm\CommissionTriggered;
use App\Jobs\Mlm\CalculateCommissionsJob;
use App\Jobs\Mlm\ProcessCommissionJob;
use App\Models\Mlm\MlmCommission;
use App\Services\Mlm\Calculators\LevelCommissionCalculator;
use App\Services\Mlm\Calculators\OriginatorJoiningCalculator;
use App\Services\Mlm\Calculators\OriginatorRecurringCalculator;
use App\Services\Mlm\Calculators\SponsorBonusCalculator;
use App\Services\Mlm\Calculators\TaskCompletionCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Commission Processor Service (Orchestrator)
 *
 * Central service for processing MLM commissions. Follows these patterns:
 * - Strategy Pattern: Multiple calculators for different commission types
 * - Event-Driven: Dispatches events at each stage for real-time updates
 * - Pipeline: Calculators run in priority order
 * - Async: Supports both sync and async processing via jobs
 *
 * Usage:
 * ```php
 * $processor = app(CommissionProcessorService::class);
 *
 * // Sync processing (immediate)
 * $commissions = $processor->processAndPersist($subscription);
 *
 * // Async processing (via queue)
 * $processor->processAsync($subscription);
 *
 * // Calculate only (no persistence)
 * $results = $processor->calculate($subscription);
 * ```
 */
final class CommissionProcessorService
{
    /**
     * @var array<int, CommissionCalculator>
     */
    private array $calculators = [];

    private readonly MlmConfigService $configService;

    public function __construct(?MlmConfigService $configService = null)
    {
        $this->configService = $configService ?? new MlmConfigService;
        $this->registerDefaultCalculators();
    }

    /**
     * Register default commission calculators
     */
    private function registerDefaultCalculators(): void
    {
        // Register built-in calculators
        $this->register(new SponsorBonusCalculator($this->configService));
        $this->register(new LevelCommissionCalculator);
        $this->register(new OriginatorJoiningCalculator($this->configService));
        $this->register(new OriginatorRecurringCalculator($this->configService));
        $this->register(new TaskCompletionCalculator);
    }

    /**
     * Register a commission calculator
     */
    public function register(CommissionCalculator $calculator): self
    {
        $this->calculators[] = $calculator;

        // Sort by priority (highest first)
        usort($this->calculators, fn ($a, $b) => $b->getPriority() <=> $a->getPriority());

        return $this;
    }

    /**
     * Get all registered calculators
     *
     * @return array<int, CommissionCalculator>
     */
    public function getCalculators(): array
    {
        return $this->calculators;
    }

    /**
     * Get enabled calculators
     *
     * @return array<int, CommissionCalculator>
     */
    public function getEnabledCalculators(): array
    {
        return array_filter($this->calculators, fn ($c) => $c->isEnabled());
    }

    /**
     * Process trigger asynchronously via queue
     *
     * This method dispatches the job and returns immediately.
     * Real-time updates are sent via broadcast events.
     */
    public function processAsync(CommissionTrigger $trigger, bool $persistImmediately = true): void
    {
        Log::channel('mlm')->info('CommissionProcessor: Dispatching async job', [
            'trigger_type' => $trigger->getTriggerType(),
            'trigger_id' => $trigger->getId(),
        ]);

        // Dispatch event for real-time UI feedback
        CommissionTriggered::dispatch($trigger, true);

        // Dispatch job to queue
        CalculateCommissionsJob::dispatch($trigger, $persistImmediately);
    }

    /**
     * Calculate commissions for a trigger (no persistence)
     *
     * @return Collection<int, CommissionResult>
     */
    public function calculate(CommissionTrigger $trigger): Collection
    {
        $results = collect();

        Log::channel('mlm')->info('CommissionProcessor: Starting calculation', [
            'trigger_type' => $trigger->getTriggerType(),
            'trigger_id' => $trigger->getId(),
            'amount' => $trigger->getCommissionableAmount(),
        ]);

        // Dispatch triggered event
        CommissionTriggered::dispatch($trigger, false);

        foreach ($this->calculators as $calculator) {
            // Skip disabled calculators
            if (! $calculator->isEnabled()) {
                continue;
            }

            // Skip if calculator doesn't support this trigger
            if (! $calculator->supports($trigger)) {
                continue;
            }

            try {
                $calculatorResults = $calculator->calculate($trigger);

                Log::channel('mlm')->debug('Calculator executed', [
                    'calculator' => $calculator::class,
                    'type' => $calculator->getCommissionType(),
                    'results_count' => $calculatorResults->count(),
                ]);

                $results = $results->merge($calculatorResults);
            } catch (\Throwable $e) {
                Log::channel('mlm')->error('Calculator failed', [
                    'calculator' => $calculator::class,
                    'error' => $e->getMessage(),
                    'trigger_id' => $trigger->getId(),
                ]);

                // Continue with other calculators
            }
        }

        // Dispatch calculated event
        CommissionsCalculated::dispatch($trigger, $results);

        Log::channel('mlm')->info('CommissionProcessor: Calculation complete', [
            'trigger_id' => $trigger->getId(),
            'total_results' => $results->count(),
            'total_amount' => $results->sum('grossAmount'),
        ]);

        return $results;
    }

    /**
     * Calculate and persist commissions (synchronous)
     *
     * @return Collection<int, MlmCommission>
     */
    public function processAndPersist(CommissionTrigger $trigger): Collection
    {
        $results = $this->calculate($trigger);

        if ($results->isEmpty()) {
            return collect();
        }

        return $this->persistResults($results);
    }

    /**
     * Persist commission results to database
     *
     * @param  Collection<int, CommissionResult>  $results
     * @return Collection<int, MlmCommission>
     */
    public function persistResults(Collection $results): Collection
    {
        $commissions = collect();

        DB::transaction(function () use ($results, &$commissions) {
            foreach ($results as $result) {
                // Apply deductions
                $result = $this->applyDeductions($result);

                // Check for duplicates
                if ($this->isDuplicate($result)) {
                    Log::channel('mlm')->warning('Duplicate commission skipped', [
                        'recipient_id' => $result->recipientId,
                        'type' => $result->type,
                    ]);

                    continue;
                }

                // Create commission record
                $commission = MlmCommission::create($result->toArray());
                $commissions->push($commission);

                // Dispatch processed event for real-time updates
                CommissionProcessed::dispatch($commission);
            }
        });

        Log::channel('mlm')->info('CommissionProcessor: Persistence complete', [
            'commissions_created' => $commissions->count(),
            'total_gross' => $commissions->sum('gross_amount'),
            'total_net' => $commissions->sum('net_amount'),
        ]);

        return $commissions;
    }

    /**
     * Persist results asynchronously via individual jobs
     *
     * @param  Collection<int, CommissionResult>  $results
     */
    public function persistResultsAsync(Collection $results): void
    {
        $jobs = $results->map(fn ($result) => new ProcessCommissionJob($result));

        Bus::batch($jobs)
            ->name('commission-persistence')
            ->onQueue('commissions')
            ->dispatch();
    }

    /**
     * Apply deductions (TDS, admin fee) to commission result
     */
    private function applyDeductions(CommissionResult $result): CommissionResult
    {
        // Skip if already applied
        if ($result->tdsAmount > 0 || $result->adminFee > 0) {
            return $result;
        }

        // Get monthly total for TDS threshold
        $monthlyTotal = (int) MlmCommission::query()
            ->where('user_id', $result->recipientId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('gross_amount');

        $tdsAmount = $this->configService->calculateTds($result->grossAmount, $monthlyTotal);
        $adminFee = $this->configService->calculateAdminFee($result->grossAmount);

        return $result->withDeductions($tdsAmount, $adminFee);
    }

    /**
     * Check if commission already exists
     */
    private function isDuplicate(CommissionResult $result): bool
    {
        if (! $result->commissionableType || ! $result->commissionableId) {
            return false;
        }

        return MlmCommission::query()
            ->where('user_id', $result->recipientId)
            ->where('type', $result->type)
            ->where('commissionable_type', $result->commissionableType)
            ->where('commissionable_id', $result->commissionableId)
            ->when($result->level !== null, fn ($q) => $q->where('level', $result->level))
            ->exists();
    }

    /**
     * Get commission statistics for a user
     *
     * @return array<string, mixed>
     */
    public function getUserStats(int $userId): array
    {
        $query = MlmCommission::query()->where('user_id', $userId);

        return [
            'total_earned' => (int) (clone $query)->sum('gross_amount'),
            'total_net' => (int) (clone $query)->sum('net_amount'),
            'total_tds' => (int) (clone $query)->sum('tds_amount'),
            'commission_count' => (clone $query)->count(),
            'this_month' => (int) (clone $query)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('net_amount'),
            'by_type' => (clone $query)
                ->selectRaw('type, SUM(gross_amount) as total, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->keyBy('type')
                ->toArray(),
        ];
    }

    /**
     * Simulate commission calculation (for preview, no persistence)
     *
     * @return array<string, mixed>
     */
    public function simulate(CommissionTrigger $trigger): array
    {
        $results = $this->calculate($trigger);

        return [
            'trigger' => [
                'type' => $trigger->getTriggerType(),
                'id' => $trigger->getId(),
                'amount' => $trigger->getCommissionableAmount(),
            ],
            'results' => $results->map(fn ($r) => [
                'recipient_id' => $r->recipientId,
                'type' => $r->type,
                'gross_amount' => $r->grossAmount,
                'level' => $r->level,
                'rate_percent' => $r->ratePercent,
                'description' => $r->description,
            ])->toArray(),
            'summary' => [
                'total_commissions' => $results->count(),
                'total_gross' => $results->sum('grossAmount'),
                'by_type' => $results->groupBy('type')->map->sum('grossAmount')->toArray(),
            ],
        ];
    }

    /**
     * Get processor configuration summary
     *
     * @return array<string, mixed>
     */
    public function getConfigSummary(): array
    {
        return [
            'registered_calculators' => count($this->calculators),
            'enabled_calculators' => count($this->getEnabledCalculators()),
            'calculators' => collect($this->calculators)->map(fn ($c) => [
                'class' => $c::class,
                'type' => $c->getCommissionType(),
                'priority' => $c->getPriority(),
                'enabled' => $c->isEnabled(),
            ])->toArray(),
            'config' => $this->configService->getConfigSummary(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

use App\Dto\Affiliate\CommissionResult;
use App\Models\Affiliate\AffiliateCommission;
use Illuminate\Support\Collection;

/**
 * Contract for Commission Processor Service
 *
 * The commission processor is the central orchestrator for all Affiliate commission
 * calculations. It coordinates multiple calculators and handles both sync
 * and async processing.
 *
 * Key responsibilities:
 * - Register and manage commission calculators
 * - Orchestrate commission calculations
 * - Persist commission results (sync or async)
 * - Provide commission statistics
 *
 * Scalability considerations:
 * - All heavy operations should be queue-able
 * - Batch operations for large result sets
 * - Idempotent operations for retry safety
 */
interface CommissionProcessorServiceInterface
{
    /**
     * Register a commission calculator
     */
    public function register(CommissionCalculator $calculator): self;

    /**
     * Get all registered calculators
     *
     * @return array<int, CommissionCalculator>
     */
    public function getCalculators(): array;

    /**
     * Get only enabled calculators
     *
     * @return array<int, CommissionCalculator>
     */
    public function getEnabledCalculators(): array;

    /**
     * Process trigger asynchronously via queue
     *
     * This method dispatches job(s) and returns immediately.
     * For 1B+ users, this is the preferred method.
     */
    public function processAsync(CommissionTrigger $trigger, bool $persistImmediately = true): void;

    /**
     * Calculate commissions for a trigger (no persistence)
     *
     * Returns calculated results without saving to database.
     * Useful for preview/simulation.
     *
     * @return Collection<int, CommissionResult>
     */
    public function calculate(CommissionTrigger $trigger): Collection;

    /**
     * Calculate and persist commissions synchronously
     *
     * WARNING: For large networks, use processAsync() instead.
     *
     * @return Collection<int, AffiliateCommission>
     */
    public function processAndPersist(CommissionTrigger $trigger): Collection;

    /**
     * Persist calculated results to database
     *
     * @param  Collection<int, CommissionResult>  $results
     * @return Collection<int, AffiliateCommission>
     */
    public function persistResults(Collection $results): Collection;

    /**
     * Persist results asynchronously via batch jobs
     *
     * Recommended for large result sets (100+ commissions).
     *
     * @param  Collection<int, CommissionResult>  $results
     */
    public function persistResultsAsync(Collection $results): void;

    /**
     * Get commission statistics for a user
     *
     * @return array{
     *     total_earned: int,
     *     total_net: int,
     *     total_tds: int,
     *     commission_count: int,
     *     this_month: int,
     *     by_type: array<string, array{total: int, count: int}>
     * }
     */
    public function getUserStats(int $userId): array;

    /**
     * Simulate commission calculation (preview, no persistence)
     *
     * @return array{
     *     trigger: array{type: string, id: int, amount: int},
     *     results: array<int, array>,
     *     summary: array{total_commissions: int, total_gross: int, by_type: array<string, int>}
     * }
     */
    public function simulate(CommissionTrigger $trigger): array;

    /**
     * Get processor configuration summary
     *
     * @return array<string, mixed>
     */
    public function getConfigSummary(): array;
}

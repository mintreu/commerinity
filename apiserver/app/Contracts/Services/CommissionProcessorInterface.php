<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Contracts\Mlm\CommissionableEntity;
use App\Contracts\Mlm\CommissionCalculator;
use App\Dto\Mlm\CommissionResult;
use App\Models\User;
use Illuminate\Support\Collection;

interface CommissionProcessorInterface
{
    /**
     * Register a commission calculator
     */
    public function register(CommissionCalculator $calculator): self;

    /**
     * Get all registered calculators
     *
     * @return Collection<CommissionCalculator>
     */
    public function getCalculators(): Collection;

    /**
     * Get only enabled calculators
     *
     * @return Collection<CommissionCalculator>
     */
    public function getEnabledCalculators(): Collection;

    /**
     * Process commissions asynchronously (queue)
     */
    public function processAsync(CommissionableEntity $entity, User $user): void;

    /**
     * Calculate commissions without persisting
     *
     * @return Collection<CommissionResult>
     */
    public function calculate(CommissionableEntity $entity, User $user): Collection;

    /**
     * Calculate and persist commissions
     *
     * @return Collection<CommissionResult>
     */
    public function processAndPersist(CommissionableEntity $entity, User $user): Collection;

    /**
     * Persist commission results to database
     */
    public function persistResults(Collection $results, CommissionableEntity $entity): void;

    /**
     * Persist results asynchronously
     */
    public function persistResultsAsync(Collection $results, CommissionableEntity $entity): void;

    /**
     * Get commission statistics for user
     *
     * @return array{
     *   total_earned: int,
     *   pending: int,
     *   paid: int,
     *   by_type: array
     * }
     */
    public function getUserStats(User $user): array;

    /**
     * Simulate commissions without processing
     *
     * @return array{
     *   results: Collection,
     *   total: int,
     *   breakdown: array
     * }
     */
    public function simulate(CommissionableEntity $entity, User $user): array;

    /**
     * Get configuration summary
     *
     * @return array{
     *   enabled_calculators: array,
     *   disabled_calculators: array,
     *   config: array
     * }
     */
    public function getConfigSummary(): array;
}

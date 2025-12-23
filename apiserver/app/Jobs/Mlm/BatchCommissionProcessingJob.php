<?php

declare(strict_types=1);

namespace App\Jobs\Mlm;

use App\Models\Membership\UserSubscription;
use App\Models\User;
use App\Notifications\Mlm\CommissionProcessingCompletedNotification;
use App\Services\Mlm\CommissionProcessorService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Scheduled batch job for processing large commission calculations
 *
 * This job is designed to run on a schedule (e.g., daily, hourly)
 * to process pending commissions in batches. It handles:
 * - Monthly recurring commissions
 * - Originator recurring commissions
 * - Bulk settlement processing
 * - Performance-optimized batch processing
 *
 * Schedule in routes/console.php:
 * Schedule::job(new BatchCommissionProcessingJob('daily'))->dailyAt('02:00');
 * Schedule::job(new BatchCommissionProcessingJob('hourly'))->hourly();
 */
final class BatchCommissionProcessingJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 3600; // 1 hour

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 3600;

    public function __construct(
        public readonly string $processType = 'daily',
        public readonly int $chunkSize = 100,
        public readonly bool $sendNotification = true,
    ) {
        $this->onQueue('commissions-batch');
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return "batch-commission-{$this->processType}-".now()->format('Y-m-d-H');
    }

    /**
     * Execute the job.
     */
    public function handle(CommissionProcessorService $processor): void
    {
        $startTime = microtime(true);
        $stats = [
            'process_type' => $this->processType,
            'started_at' => now()->toIso8601String(),
            'items_processed' => 0,
            'commissions_created' => 0,
            'total_amount' => 0,
            'errors' => [],
        ];

        Log::channel('mlm')->info('BatchCommissionProcessingJob started', [
            'process_type' => $this->processType,
            'chunk_size' => $this->chunkSize,
        ]);

        try {
            match ($this->processType) {
                'hourly' => $this->processHourlyCommissions($processor, $stats),
                'daily' => $this->processDailyCommissions($processor, $stats),
                'weekly' => $this->processWeeklyCommissions($processor, $stats),
                'monthly' => $this->processMonthlyCommissions($processor, $stats),
                default => throw new \InvalidArgumentException("Unknown process type: {$this->processType}"),
            };

            $stats['completed_at'] = now()->toIso8601String();
            $stats['duration_seconds'] = round(microtime(true) - $startTime, 2);

            Log::channel('mlm')->info('BatchCommissionProcessingJob completed', $stats);

            // Send notification to admins
            if ($this->sendNotification) {
                $this->notifyAdmins($stats);
            }
        } catch (\Throwable $e) {
            $stats['error'] = $e->getMessage();
            $stats['failed_at'] = now()->toIso8601String();

            Log::channel('mlm')->error('BatchCommissionProcessingJob failed', [
                ...$stats,
                'trace' => $e->getTraceAsString(),
            ]);

            // Notify admins of failure
            if ($this->sendNotification) {
                $this->notifyAdmins($stats, failed: true);
            }

            throw $e;
        }
    }

    /**
     * Process hourly commissions (real-time catch-up)
     */
    private function processHourlyCommissions(CommissionProcessorService $processor, array &$stats): void
    {
        // Process any pending subscriptions that haven't had commissions calculated
        $pendingSubscriptions = UserSubscription::query()
            ->where('status', 'active')
            ->whereDoesntHave('commissions')
            ->where('created_at', '>=', now()->subHours(2))
            ->orderBy('created_at')
            ->cursor();

        foreach ($pendingSubscriptions as $subscription) {
            try {
                $commissions = $processor->processAndPersist($subscription);
                $stats['items_processed']++;
                $stats['commissions_created'] += $commissions->count();
                $stats['total_amount'] += $commissions->sum('gross_amount');
            } catch (\Throwable $e) {
                $stats['errors'][] = [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Process daily commissions
     */
    private function processDailyCommissions(CommissionProcessorService $processor, array &$stats): void
    {
        // Process originator recurring commissions (on_withdrawal type)
        $this->processOriginatorRecurring($processor, $stats);

        // Process any missed commissions from yesterday
        $this->processMissedCommissions($processor, $stats);
    }

    /**
     * Process weekly commissions
     */
    private function processWeeklyCommissions(CommissionProcessorService $processor, array &$stats): void
    {
        // Process weekly bonuses, matching bonuses, etc.
        $this->processMatchingBonuses($processor, $stats);
    }

    /**
     * Process monthly commissions
     */
    private function processMonthlyCommissions(CommissionProcessorService $processor, array &$stats): void
    {
        // Monthly settlement - batch process all accumulated commissions
        $this->processMonthlySettlement($processor, $stats);

        // Process agent salaries based on KPIs
        $this->processAgentSalaries($processor, $stats);

        // Process pool bonuses
        $this->processPoolBonuses($processor, $stats);
    }

    /**
     * Process originator recurring commissions
     */
    private function processOriginatorRecurring(CommissionProcessorService $processor, array &$stats): void
    {
        if (! config('mlm.originator_commissions.recurring_commission.enabled', false)) {
            return;
        }

        // This would be implemented based on withdrawal triggers or monthly income
        Log::channel('mlm')->info('Processing originator recurring commissions');
    }

    /**
     * Process missed commissions (catch-up processing)
     */
    private function processMissedCommissions(CommissionProcessorService $processor, array &$stats): void
    {
        // Find subscriptions from last 24 hours without commissions
        $missed = UserSubscription::query()
            ->where('status', 'active')
            ->whereDoesntHave('commissions')
            ->where('created_at', '>=', now()->subDay())
            ->get();

        foreach ($missed as $subscription) {
            try {
                $commissions = $processor->processAndPersist($subscription);
                $stats['items_processed']++;
                $stats['commissions_created'] += $commissions->count();
            } catch (\Throwable $e) {
                $stats['errors'][] = [
                    'type' => 'missed',
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Process matching bonuses
     */
    private function processMatchingBonuses(CommissionProcessorService $processor, array &$stats): void
    {
        if (! config('mlm.member_commissions.matching_bonus.enabled', false)) {
            return;
        }

        Log::channel('mlm')->info('Processing matching bonuses');
        // Implementation based on business rules
    }

    /**
     * Process monthly settlement
     */
    private function processMonthlySettlement(CommissionProcessorService $processor, array &$stats): void
    {
        Log::channel('mlm')->info('Processing monthly settlement');
        // Monthly settlement logic - consolidate, apply caps, etc.
    }

    /**
     * Process agent salaries based on KPIs
     */
    private function processAgentSalaries(CommissionProcessorService $processor, array &$stats): void
    {
        if (! config('mlm.agent_salary.enabled', false)) {
            return;
        }

        Log::channel('mlm')->info('Processing agent salaries');
        // Agent salary calculation based on originated users and team sales
    }

    /**
     * Process pool bonuses
     */
    private function processPoolBonuses(CommissionProcessorService $processor, array &$stats): void
    {
        if (! config('mlm.member_commissions.pool_bonus.enabled', false)) {
            return;
        }

        Log::channel('mlm')->info('Processing pool bonuses');
        // Pool bonus distribution logic
    }

    /**
     * Notify admins about batch processing completion
     */
    private function notifyAdmins(array $stats, bool $failed = false): void
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->orWhere('role', 'admin')
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send(
            $admins,
            new CommissionProcessingCompletedNotification($stats, $failed)
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('mlm')->critical('BatchCommissionProcessingJob permanently failed', [
            'process_type' => $this->processType,
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'mlm',
            'batch-processing',
            "type:{$this->processType}",
        ];
    }
}

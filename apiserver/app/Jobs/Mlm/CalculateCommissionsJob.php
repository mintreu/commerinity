<?php

declare(strict_types=1);

namespace App\Jobs\Mlm;

use App\Contracts\Mlm\CommissionTrigger;
use App\Events\Mlm\CommissionsCalculated;
use App\Services\Mlm\CommissionProcessorService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * Job to calculate commissions asynchronously
 *
 * This job runs all commission calculators for a trigger event.
 * Results are dispatched via CommissionsCalculated event.
 */
final class CalculateCommissionsJob implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    public function __construct(
        public readonly CommissionTrigger $trigger,
        public readonly bool $persistImmediately = true,
    ) {
        $this->onQueue('commissions');
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        // Prevent duplicate processing of same trigger
        return [
            (new WithoutOverlapping($this->getOverlapKey()))
                ->dontRelease()
                ->expireAfter(180),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(CommissionProcessorService $processor): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        Log::channel('mlm')->info('CalculateCommissionsJob started', [
            'trigger_type' => $this->trigger->getTriggerType(),
            'trigger_id' => $this->trigger->getId(),
            'user_id' => $this->trigger->getTriggeringUserId(),
            'amount' => $this->trigger->getCommissionableAmount(),
        ]);

        try {
            if ($this->persistImmediately) {
                // Calculate and persist in one go
                $commissions = $processor->processAndPersist($this->trigger);

                Log::channel('mlm')->info('CalculateCommissionsJob completed with persistence', [
                    'trigger_id' => $this->trigger->getId(),
                    'commissions_created' => $commissions->count(),
                    'total_amount' => $commissions->sum('gross_amount'),
                ]);
            } else {
                // Only calculate, return results for further processing
                $results = $processor->calculate($this->trigger);

                CommissionsCalculated::dispatch($this->trigger, $results, [
                    'job_id' => $this->job?->getJobId(),
                    'queue' => $this->queue,
                ]);

                Log::channel('mlm')->info('CalculateCommissionsJob completed (calculation only)', [
                    'trigger_id' => $this->trigger->getId(),
                    'results_count' => $results->count(),
                    'total_amount' => $results->sum('grossAmount'),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('mlm')->error('CalculateCommissionsJob failed', [
                'trigger_id' => $this->trigger->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('mlm')->critical('CalculateCommissionsJob permanently failed', [
            'trigger_type' => $this->trigger->getTriggerType(),
            'trigger_id' => $this->trigger->getId(),
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Get unique key for overlap prevention
     */
    private function getOverlapKey(): string
    {
        return sprintf(
            'commission-calc-%s-%d',
            $this->trigger->getTriggerType(),
            $this->trigger->getId()
        );
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
            'commission-calculation',
            "trigger:{$this->trigger->getTriggerType()}",
            "user:{$this->trigger->getTriggeringUserId()}",
        ];
    }
}

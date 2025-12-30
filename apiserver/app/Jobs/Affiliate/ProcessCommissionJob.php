<?php

declare(strict_types=1);

namespace App\Jobs\Affiliate;

use App\Dto\Affiliate\CommissionResult;
use App\Events\Affiliate\CommissionProcessed;
use App\Models\Affiliate\AffiliateCommission;
use App\Services\Affiliate\AffiliateConfigService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job to persist a single commission result
 *
 * Handles deductions (TDS, admin fee), wallet credits,
 * and broadcasts the CommissionProcessed event.
 */
final class ProcessCommissionJob implements ShouldQueue
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
    public int $timeout = 60;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 5;

    public function __construct(
        public readonly CommissionResult $result,
        public readonly bool $creditWallet = true,
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
        // Prevent duplicate commission creation
        return [
            (new WithoutOverlapping($this->getOverlapKey()))
                ->dontRelease()
                ->expireAfter(60),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(AffiliateConfigService $configService): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        Log::channel('affiliate')->info('ProcessCommissionJob started', [
            'recipient_id' => $this->result->recipientId,
            'type' => $this->result->type,
            'gross_amount' => $this->result->grossAmount,
        ]);

        try {
            $commission = DB::transaction(function () use ($configService) {
                // Apply deductions if not already applied
                $result = $this->applyDeductionsIfNeeded($this->result, $configService);

                // Check for duplicate commission
                if ($this->isDuplicateCommission($result)) {
                    Log::channel('affiliate')->warning('Duplicate commission detected, skipping', [
                        'recipient_id' => $result->recipientId,
                        'type' => $result->type,
                        'commissionable_type' => $result->commissionableType,
                        'commissionable_id' => $result->commissionableId,
                    ]);

                    return null;
                }

                // Create the commission record
                $commission = AffiliateCommission::create($result->toArray());

                // Credit wallet if enabled
                if ($this->creditWallet && $result->netAmount > 0) {
                    $this->creditUserWallet($commission);
                }

                return $commission;
            });

            if ($commission) {
                // Dispatch event for real-time updates
                CommissionProcessed::dispatch($commission);

                Log::channel('affiliate')->info('ProcessCommissionJob completed', [
                    'commission_id' => $commission->id,
                    'recipient_id' => $commission->user_id,
                    'net_amount' => $commission->net_amount,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('affiliate')->error('ProcessCommissionJob failed', [
                'recipient_id' => $this->result->recipientId,
                'type' => $this->result->type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Apply TDS and admin fee deductions if not already calculated
     */
    private function applyDeductionsIfNeeded(
        CommissionResult $result,
        AffiliateConfigService $configService,
    ): CommissionResult {
        // If deductions already applied (non-zero), return as-is
        if ($result->tdsAmount > 0 || $result->adminFee > 0) {
            return $result;
        }

        // Get user's monthly total for TDS threshold
        $monthlyTotal = $this->getMonthlyTotal($result->recipientId);

        // Calculate TDS
        $tdsAmount = $configService->calculateTds($result->grossAmount, $monthlyTotal);

        // Calculate admin fee (if applicable)
        $adminFee = $configService->calculateAdminFee($result->grossAmount);

        return $result->withDeductions($tdsAmount, $adminFee);
    }

    /**
     * Get user's monthly commission total for TDS calculation
     */
    private function getMonthlyTotal(int $userId): int
    {
        return AffiliateCommission::query()
            ->where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('gross_amount');
    }

    /**
     * Check if this commission already exists
     */
    private function isDuplicateCommission(CommissionResult $result): bool
    {
        if (! $result->commissionableType || ! $result->commissionableId) {
            return false;
        }

        return AffiliateCommission::query()
            ->where('user_id', $result->recipientId)
            ->where('type', $result->type)
            ->where('commissionable_type', $result->commissionableType)
            ->where('commissionable_id', $result->commissionableId)
            ->when($result->level !== null, fn ($q) => $q->where('level', $result->level))
            ->exists();
    }

    /**
     * Credit the user's wallet with commission amount
     */
    private function creditUserWallet(AffiliateCommission $commission): void
    {
        // TODO: Implement wallet credit logic
        // This will be implemented when Wallet service is ready
        // $walletService->credit($commission->user_id, $commission->net_amount, [
        //     'type' => 'commission',
        //     'reference_type' => AffiliateCommission::class,
        //     'reference_id' => $commission->id,
        //     'description' => $commission->description,
        // ]);

        Log::channel('affiliate')->info('Wallet credit pending implementation', [
            'commission_id' => $commission->id,
            'amount' => $commission->net_amount,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('affiliate')->critical('ProcessCommissionJob permanently failed', [
            'recipient_id' => $this->result->recipientId,
            'type' => $this->result->type,
            'gross_amount' => $this->result->grossAmount,
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Get unique key for overlap prevention
     */
    private function getOverlapKey(): string
    {
        return sprintf(
            'commission-process-%d-%s-%s-%s',
            $this->result->recipientId,
            $this->result->type,
            $this->result->commissionableType ?? 'null',
            $this->result->commissionableId ?? '0'
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
            'affiliate',
            'commission-process',
            "type:{$this->result->type}",
            "user:{$this->result->recipientId}",
        ];
    }
}

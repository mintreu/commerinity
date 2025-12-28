<?php

declare(strict_types=1);

namespace App\Console\Commands\Ecommerce;

use App\Casts\OrderStatusCast;
use App\Models\Ecommerce\Order;
use App\Services\Mlm\CommissionProcessorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Complete Delivered Orders Command
 *
 * Runs on schedule to transition DELIVERED orders to COMPLETED
 * after their return period has expired.
 *
 * Order Flow:
 * DELIVERED → (return period expires) → COMPLETED → (MLM commission triggered)
 */
class CompleteDeliveredOrders extends Command
{
    protected $signature = 'ecommerce:complete-orders
                            {--dry-run : Show what would be processed without making changes}
                            {--limit=100 : Maximum number of orders to process per run}';

    protected $description = 'Complete delivered orders after return period expires and trigger MLM commissions';

    public function __construct(
        private readonly CommissionProcessorService $commissionProcessor,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info($isDryRun ? '[DRY RUN] ' : ''.'Processing delivered orders ready for completion...');

        $orders = Order::readyForCompletion()
            ->with(['customerable', 'items.product'])
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders ready for completion.');

            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} order(s) ready for completion.");

        $completed = 0;
        $commissionsProcessed = 0;
        $errors = 0;

        foreach ($orders as $order) {
            try {
                if ($isDryRun) {
                    $this->line("  [DRY RUN] Would complete order #{$order->order_number}");
                    $completed++;

                    continue;
                }

                DB::transaction(function () use ($order, &$completed, &$commissionsProcessed) {
                    // Update order status to COMPLETED
                    $order->update([
                        'status' => OrderStatusCast::COMPLETED->value,
                        'completed_at' => now(),
                    ]);

                    $this->line("  ✓ Completed order #{$order->order_number}");
                    $completed++;

                    // Process MLM commission if eligible
                    if ($order->canGenerateCommission() && ! $order->isCommissionProcessed()) {
                        $this->commissionProcessor->processCommission($order);
                        $order->markCommissionProcessed();
                        $commissionsProcessed++;
                        $this->line("    → MLM commission processed (BV: {$order->total_bv})");
                    }
                });
            } catch (\Throwable $e) {
                $errors++;
                $this->error("  ✗ Failed to complete order #{$order->order_number}: {$e->getMessage()}");
                Log::error('CompleteDeliveredOrders failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Orders completed: {$completed}");
        $this->line("  Commissions processed: {$commissionsProcessed}");
        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}

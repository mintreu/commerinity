<?php

declare(strict_types=1);

namespace App\Jobs\Affiliate;

use App\Services\Affiliate\AffiliatePayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

final class ProcessMonthlyDisbursementJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?string $periodEnd = null
    ) {
    }

    public function handle(AffiliatePayoutService $service): void
    {
        $periodEnd = $this->periodEnd ? Carbon::parse($this->periodEnd) : null;
        $service->processMonthly($periodEnd);
    }
}

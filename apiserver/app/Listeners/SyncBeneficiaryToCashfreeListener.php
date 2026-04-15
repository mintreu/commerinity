<?php

namespace App\Listeners;

use App\Events\BeneficiarySyncRequested;
use App\Services\BeneficiaryAccountService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SyncBeneficiaryToCashfreeListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BeneficiarySyncRequested $event): void
    {
        try {
            BeneficiaryAccountService::make($event->beneficiaryAccount)->sync();
        } catch (\Throwable $e) {
            Log::error('Async beneficiary sync failed', [
                'uuid' => $event->beneficiaryAccount->uuid,
                'error' => $e->getMessage(),
            ]);

            throw $e; // retry trigger
        }
    }
}

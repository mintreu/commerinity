<?php

use App\Jobs\Wallet\CheckPayoutStatusJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
*/

// Check pending payout status every 15 minutes
Schedule::job(new CheckPayoutStatusJob)
    ->everyFifteenMinutes()
    ->name('check-payout-status')
    ->withoutOverlapping();

// Complete delivered orders after return period expires (runs hourly)
// Triggers MLM commissions on COMPLETED status
Schedule::command('ecommerce:complete-orders')
    ->hourly()
    ->name('complete-delivered-orders')
    ->withoutOverlapping()
    ->runInBackground();

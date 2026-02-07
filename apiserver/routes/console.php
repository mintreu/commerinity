<?php

use App\Jobs\Wallet\CheckPayoutStatusJob;
use App\Jobs\Affiliate\ProcessMonthlyDisbursementJob;
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
// Triggers Affiliate commissions on COMPLETED status
Schedule::command('ecommerce:complete-orders')
    ->hourly()
    ->name('complete-delivered-orders')
    ->withoutOverlapping()
    ->runInBackground();

// Reindex sales (sale_products) hourly to keep targets in sync
Schedule::command('app:sales-reindex')
    ->hourly()
    ->name('sales-reindex')
    ->withoutOverlapping()
    ->runInBackground();

// Archive transactions older than 1 year (daily)
Schedule::command('transactions:archive --days=365')
    ->daily()
    ->name('transactions-archive')
    ->withoutOverlapping()
    ->runInBackground();

// Monthly affiliate disbursement to wallet (1st day, 01:30 AM)
Schedule::job(new ProcessMonthlyDisbursementJob)
    ->monthlyOn(1, '01:30')
    ->name('affiliate-monthly-disbursement')
    ->withoutOverlapping()
    ->runInBackground();

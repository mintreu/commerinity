<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\BeneficiaryAccount;
use App\Models\Transaction;
use App\Observers\AddressObserver;
use App\Observers\BeneficiaryAccountObserver;
use App\Observers\TransactionObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Register model observers
        Transaction::observe(TransactionObserver::class);
        BeneficiaryAccount::observe(BeneficiaryAccountObserver::class);
        Address::observe(AddressObserver::class);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->input('email') ?? $request->input('mobile') ?? $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many reset attempts. Please try again later.',
                    ], 429);
                });
        });
    }
}

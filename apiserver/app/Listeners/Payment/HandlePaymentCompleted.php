<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Casts\JobApplicationStatusCast;
use App\Events\PaymentCompleted;
use App\Models\Membership\UserSubscription;
use App\Models\Recruitment\JobApplication;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Membership\SubscriptionService;
use App\Services\MoneyService;
use App\Services\UserServices\UserAffiliateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * HandlePaymentCompleted - Routes confirmed payments to appropriate handlers
 *
 * Handles:
 * - Wallet TopUp → Update balance
 * - Subscription → Activate membership + Affiliate
 * - JobApplication → Submit application
 * - Order → Confirm order (future)
 */
final class HandlePaymentCompleted
{
    /**
     * Handle the event
     */
    public function handle(PaymentCompleted $event): void
    {
        $transaction = $event->transaction;
        $transaction->load('transactionable');

        $payable = $transaction->transactionable;

        if (! $payable) {
            Log::warning('Payment completed but no transactionable found', [
                'transaction_id' => $transaction->uuid,
            ]);

            return;
        }

        Log::info('Processing payment completion', [
            'transaction_id' => $transaction->uuid,
            'payable_type' => get_class($payable),
            'payable_id' => $payable->id,
        ]);

        // Route to appropriate handler
        match (true) {
            $payable instanceof Wallet => $this->handleWalletTopup($transaction, $payable),
            $payable instanceof UserSubscription => $this->handleSubscriptionPayment($transaction, $payable),
            $payable instanceof JobApplication => $this->handleRecruitmentPayment($transaction, $payable),
            default => Log::warning('Unhandled payable type', [
                'type' => get_class($payable),
                'transaction_id' => $transaction->uuid,
            ]),
        };
    }

    /**
     * Handle wallet topup (add money to wallet)
     */
    private function handleWalletTopup(mixed $transaction, Wallet $wallet): void
    {
        DB::transaction(function () use ($transaction, $wallet) {
            $currentBalance = MoneyService::make($wallet->balance);
            $topupAmount = MoneyService::make($transaction->amount);

            // Add money to wallet
            $newBalance = $currentBalance->add($topupAmount->getAmount());

            $wallet->update([
                'balance' => $newBalance->getAmount(),
            ]);

            Log::info('Wallet topup completed', [
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->uuid,
                'old_balance' => $currentBalance->getAmount(),
                'topup_amount' => $topupAmount->getAmount(),
                'new_balance' => $newBalance->getAmount(),
            ]);
        });
    }

    /**
     * Handle subscription payment (activate membership + Affiliate)
     */
    private function handleSubscriptionPayment(mixed $transaction, UserSubscription $subscription): void
    {
        DB::transaction(function () use ($transaction, $subscription) {
            $subscription->load('user');
            $user = $subscription->user;

            // 1. Auto-placement in Affiliate tree (if has sponsor)
            if ($user->parent_id) {
                $sponsor = User::find($user->parent_id);
                $affiliateService = app(UserAffiliateService::class);
                $affiliateService->placeUser($user, $sponsor);

                Log::info('User placed in Affiliate tree', [
                    'user_id' => $user->id,
                    'sponsor_id' => $sponsor->id,
                ]);
            }

            // 2. Activate subscription + trigger commissions
            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->activateSubscription($subscription, $transaction->id);

            Log::info('Subscription activated via gateway payment', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->uuid,
                'payment_method' => $transaction->payment_method,
            ]);

            // 3. Send confirmation notification
            // $user->notify(new SubscriptionConfirmedNotification($subscription));
        });
    }

    /**
     * Handle recruitment fee payment
     */
    private function handleRecruitmentPayment(mixed $transaction, JobApplication $application): void
    {
        DB::transaction(function () use ($transaction, $application) {
            // Change status from awaiting_payment to submitted
            $application->update([
                'status' => JobApplicationStatusCast::Submitted,
                'paid_at' => now(),
            ]);

            Log::info('Recruitment fee paid, application submitted', [
                'application_id' => $application->id,
                'transaction_id' => $transaction->uuid,
            ]);

            // Notify HR/Admin
            // Notify applicant
        });
    }
}

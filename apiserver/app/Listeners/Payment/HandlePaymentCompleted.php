<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Casts\JobApplicationStatusCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Events\PaymentCompleted;
use App\Models\Membership\UserSubscription;
use App\Models\Recruitment\JobApplication;
use App\Models\Wallet;
use App\Services\Mlm\CommissionProcessingService;
use App\Services\MoneyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * HandlePaymentCompleted - Routes confirmed payments to appropriate handlers
 *
 * Handles:
 * - Wallet TopUp → Update balance
 * - Subscription → Activate membership + MLM
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
            $newBalance = $currentBalance->plus($topupAmount->getAmount());

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
     * Handle subscription payment (activate membership + MLM)
     */
    private function handleSubscriptionPayment(mixed $transaction, UserSubscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->load('user', 'level', 'stage');
            $user = $subscription->user;

            // 1. Mark subscription as paid
            $subscription->update([
                'is_paid' => true,
                'paid_at' => now(),
            ]);

            // 2. Update user status and type
            $user->update([
                'status' => UserStatusCast::ACTIVE,
                'type' => UserTypeCast::MEMBER, // Upgrade to member
                'level_id' => $subscription->level_id,
                'stage_id' => $subscription->stage_id,
            ]);

            Log::info('Subscription activated', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'level' => $subscription->level->name,
                'stage' => $subscription->stage->name,
            ]);

            // 3. Trigger MLM commission calculations (if has sponsor)
            if ($user->parent_id || $user->originator_id) {
                try {
                    $commissionService = app(CommissionProcessingService::class);
                    $commissionService->processSubscriptionCommissions($subscription);

                    Log::info('MLM commissions triggered for subscription', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    // Don't fail the subscription if commission fails
                    Log::error('Failed to process subscription commissions', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 4. Send confirmation notification
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
                'status' => JobApplicationStatusCast::SUBMITTED,
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

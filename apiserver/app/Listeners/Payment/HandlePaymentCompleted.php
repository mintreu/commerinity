<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Casts\JobApplicationStatusCast;
use App\Casts\OrderStatusCast;
use App\Casts\UserTypeCast;
use App\Events\PaymentCompleted;
use App\Models\Admin;
use App\Models\Ecommerce\Order;
use App\Models\Membership\UserSubscription;
use App\Models\Recruitment\JobApplication;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Affiliate\CommissionProcessorService;
use App\Services\Ecommerce\OrderService\OrderValidationService;
use App\Services\Membership\SubscriptionService;
use App\Services\Recruitment\JobApplicationNotificationService;
use App\Services\UserServices\UserAffiliateService;
use App\Services\Wallet\WalletTransactionNotificationService;
use Filament\Notifications\Notification;
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
        $transaction->load('transactionable','transactionable.customer');

        $payable = $transaction->transactionable;

        if (! $payable) {
            Log::warning('Payment completed but no transactionable found', [
                'transaction_id' => $transaction->uuid,
            ]);

            return;
        }

        // Eager load relationships on the payable model
        if ($payable instanceof UserSubscription) {
            $payable->load(['stage.levels', 'user', 'currentLevel', 'highestLevel','level']);
        } elseif ($payable instanceof JobApplication) {
            $payable->load(['recruitment', 'applicant']);
        }

        Log::info('Processing payment completion', [
            'transaction_id' => $transaction->uuid,
            'payable_type' => get_class($payable),
            'payable_id' => $payable->id,
        ]);

        // Route to appropriate handler
        if ($payable instanceof Wallet) {
            // Wallet top-up balance accounting is handled by TransactionObserver.
            // This handler sends user-facing notifications only.
            $this->handleWalletTopup($transaction, $payable);
            return;
        }

        if ($payable instanceof UserSubscription) {
            $this->handleSubscriptionPayment($transaction, $payable);
            return;
        }

        if ($payable instanceof JobApplication) {
            $this->handleRecruitmentPayment($transaction, $payable);
            return;
        }

        if ($payable instanceof Order) {
            $this->handleOrderConfirmation($transaction, $payable);
            return;
        }

        Log::warning('Unhandled payable type', [
            'type' => get_class($payable),
            'transaction_id' => $transaction->uuid,
        ]);

        // Notify applicant (User Side Notification eg: push notification and mail notification)
        // 1. Push Notification
        //$payable->customer->notify();
        // 2. SMS Notification

        // 3. Email Notification with Invoice when email available

        // 4. Db Notification to that user


    }

    /**
     * Handle wallet topup (add money to wallet)
     */
    private function handleWalletTopup(Transaction $transaction, Wallet $wallet): void
    {
        $notifiableUser = null;
        $notifiableTransaction = null;
        $notifiableWallet = null;

        DB::transaction(function () use ($transaction, $wallet, &$notifiableUser, &$notifiableTransaction, &$notifiableWallet) {
            $lockedTransaction = Transaction::query()
                ->lockForUpdate()
                ->with(['wallet.walletable'])
                ->find($transaction->id);

            if (! $lockedTransaction) {
                return;
            }

            $metadata = $lockedTransaction->metadata ?? [];
            if (($metadata['wallet_topup_notification_sent'] ?? false) === true) {
                Log::info('Skipping duplicate wallet top-up notification', [
                    'transaction_id' => $lockedTransaction->uuid,
                ]);

                return;
            }

            $lockedWallet = $lockedTransaction->wallet ?? $wallet;
            if (! $lockedWallet) {
                return;
            }

            $walletOwner = $lockedWallet->walletable;
            if (! $walletOwner instanceof User) {
                return;
            }

            $lockedTransaction->update([
                'metadata' => array_merge($metadata, [
                    'wallet_topup_notification_sent' => true,
                    'wallet_topup_notified_at' => now()->toIso8601String(),
                ]),
            ]);

            $notifiableUser = $walletOwner;
            $notifiableTransaction = $lockedTransaction;
            $notifiableWallet = $lockedWallet;
        });

        if ($notifiableUser instanceof User && $notifiableTransaction instanceof Transaction && $notifiableWallet instanceof Wallet) {
            app(WalletTransactionNotificationService::class)
                ->notifyTopupCompleted($notifiableUser, $notifiableTransaction, $notifiableWallet);
        }
    }

    /**
     * Handle subscription payment (activate membership + Affiliate)
     */
    private function handleSubscriptionPayment(mixed $transaction, UserSubscription $subscription): void
    {

        DB::transaction(function () use ($transaction, $subscription) {
            $subscription = UserSubscription::query()
                ->lockForUpdate()
                ->with('user')
                ->findOrFail($subscription->id);

            if ($subscription->status === UserSubscription::STATUS_ACTIVE && $subscription->is_paid) {
                Log::info('Skipping duplicate subscription payment completion', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $transaction->uuid,
                ]);

                return;
            }

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
            // NOTE: User type upgrade (REGULAR → MEMBER) happens inside activateSubscription()
            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->activateSubscription($subscription, $transaction->id);

            // Refresh user to get updated type
            $user->refresh();

            Log::info('Subscription activated via gateway payment', [
                'user_id' => $user->id,
                'user_type' => $user->type->value,
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
        $notifiableApplicant = null;

        DB::transaction(function () use ($transaction, $application, &$notifiableApplicant) {
            $application = JobApplication::query()
                ->lockForUpdate()
                ->with(['recruitment', 'applicant'])
                ->findOrFail($application->id);

            if ($application->is_paid && $application->status === JobApplicationStatusCast::Submitted) {
                Log::info('Skipping duplicate job application payment completion', [
                    'application_id' => $application->id,
                    'application_uuid' => $application->uuid,
                    'transaction_id' => $transaction->uuid,
                ]);

                return;
            }

            $application->update([
                'status' => JobApplicationStatusCast::Submitted,
                'is_paid' => true,
                'transaction_id' => $transaction->id,
                'submitted_at' => now(),
            ]);

            $application->refresh()->loadMissing(['recruitment', 'applicant']);
            if ($application->applicant instanceof User) {
                $notifiableApplicant = $application->applicant;
            }

            Log::info('Recruitment fee paid, application submitted', [
                'application_id' => $application->id,
                'application_uuid' => $application->uuid,
                'transaction_id' => $transaction->uuid,
                'status' => $application->status->value,
            ]);
        });

        Notification::make()->sendToDatabase(Admin::all())
            ->title('New Application Submitted')
            ->body('Application ID : '. $application->uuid);

        if ($notifiableApplicant instanceof User) {
            app(JobApplicationNotificationService::class)
                ->notifyPaymentConfirmed($notifiableApplicant, $application->fresh(['recruitment']));
        }
    }



    private function handleOrderConfirmation(\App\Models\Transaction $transaction, Order $payable): void
    {
        DB::transaction(function () use ($transaction, $payable) {
            $order = Order::query()
                ->lockForUpdate()
                ->find($payable->id);

            if (! $order) {
                Log::warning('Order payment completed but order not found', [
                    'transaction_id' => $transaction->uuid,
                    'order_id' => $payable->id,
                ]);

                return;
            }

            if ($order->payment_success || $this->isOrderAlreadyProcessed($order)) {
                Log::info('Skipping duplicate order payment completion', [
                    'transaction_id' => $transaction->uuid,
                    'order_id' => $order->id,
                    'order_status' => $order->status->value,
                ]);

                return;
            }

            // Get CommissionProcessorService for processing affiliate commissions
            $commissionProcessor = app(CommissionProcessorService::class);

            $orderService = OrderValidationService::make($transaction, $order, $commissionProcessor);
            $orderService->validate();
        });
    }

    private function isOrderAlreadyProcessed(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatusCast::CONFIRMED,
            OrderStatusCast::PROCESSING,
            OrderStatusCast::SHIPPED,
            OrderStatusCast::DELIVERED,
            OrderStatusCast::COMPLETED,
        ], true);
    }



}

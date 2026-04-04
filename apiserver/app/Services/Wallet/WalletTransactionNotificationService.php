<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Contracts\Services\NotificationSmsSenderInterface;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\GeneralNotification;
use App\Services\MoneyService;
use Illuminate\Support\Facades\Log;

final class WalletTransactionNotificationService
{
    public function __construct(
        private readonly NotificationSmsSenderInterface $smsService,
    ) {}

    public function notifyTopupCompleted(User $user, Transaction $transaction, Wallet $wallet): void
    {
        $title = 'Wallet Top-up Successful';
        $amount = MoneyService::format($transaction->amount);
        $balance = MoneyService::format($wallet->fresh()->balance);
        $reference = $transaction->reference_number ?: $transaction->uuid;
        $message = "{$amount} added to your wallet. Ref: {$reference}. Available balance: {$balance}.";

        $user->notify(new GeneralNotification(
            title: $title,
            message: $message,
            actionUrl: $this->walletUrl(),
            actionText: 'View Wallet',
            channels: ['database', 'mail', 'push'],
            type: 'success',
        ));

        $this->sendSms(
            user: $user,
            title: $title,
            templateSlug: 'wallet-update',
            variables: [
                'amount' => MoneyService::toRupeesString($transaction->amount),
                'action' => 'credited',
                'balance' => MoneyService::toRupeesString($wallet->fresh()->balance),
                'app_name' => (string) config('app.name'),
            ],
            context: [
                'transaction_id' => $transaction->id,
                'transaction_uuid' => $transaction->uuid,
                'wallet_id' => $wallet->id,
            ],
        );
    }

    /**
     * @param  array<string, string>  $variables
     * @param  array<string, mixed>  $context
     */
    private function sendSms(User $user, string $title, string $templateSlug, array $variables, array $context = []): void
    {
        if (! $user->mobile) {
            return;
        }

        if (! $this->smsService->canSend(1)) {
            Log::info('Skipping wallet SMS due to low SMS balance', [
                'user_id' => $user->id,
                'title' => $title,
                ...$context,
            ]);

            return;
        }

        $response = $this->smsService->sendTemplate(
            phone: $user->mobile,
            templateSlug: $templateSlug,
            variables: $variables,
            type: 'transactional',
            userId: $user->id,
        );

        if (! $response->success) {
            Log::warning('Wallet SMS notification failed', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $response->errorMessage,
                ...$context,
            ]);
        }
    }

    private function walletUrl(): string
    {
        return rtrim(config('app.client_url', 'http://localhost:3000'), '/').'/wallet';
    }
}

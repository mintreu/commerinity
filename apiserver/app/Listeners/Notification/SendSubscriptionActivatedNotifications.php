<?php

declare(strict_types=1);

namespace App\Listeners\Notification;

use App\Events\Affiliate\SubscriptionActivated;
use App\Notifications\SubscriptionActivatedNotification;
use App\Contracts\Services\NotificationSmsSenderInterface;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Queued listener that dispatches the subscription activation notification
 * and conditionally triggers a transactional SMS once the subscription settles.
 */
final class SendSubscriptionActivatedNotifications implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly NotificationSmsSenderInterface $smsService) {}

    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription->refresh()->loadMissing(['stage', 'currentLevel']);
        $user = $this->resolveUser($subscription);

        if (! $user) {
            return;
        }

        $user->notify(new SubscriptionActivatedNotification($subscription));

        if (! $user->mobile) {
            return;
        }

        if (! $this->smsService->canSend(1)) {
            Log::info('Subscription SMS skipped: insufficient balance', [
                'user_id' => $user->id,
                'stage_id' => $subscription->stage_id,
            ]);

            return;
        }

        $message = $this->buildSmsMessage($subscription, $user);
        $response = $this->smsService->sendSingle(
            $user->mobile,
            $message,
            'transactional',
            $user->id,
        );

        if (! $response->success) {
            Log::warning('Subscription SMS failed', [
                'user_id' => $user->id,
                'stage_id' => $subscription->stage_id,
                'error' => $response->errorMessage,
            ]);
        }
    }

    private function resolveUser(UserSubscription $subscription): ?User
    {
        return $subscription->user ?? $subscription->load('user')->user;
    }

    /**
     * Build the transactional SMS copy that the user will receive.
     */
    private function buildSmsMessage(UserSubscription $subscription, User $user): string
    {
        $stage = $subscription->stage?->name ?? 'your membership';
        $level = $subscription->currentLevel?->full_name ?? $stage;

        return "Your {$stage} ({$level}) subscription is active. Visit ".config('app.client_url', config('app.url'))." to unlock deals.";
    }
}

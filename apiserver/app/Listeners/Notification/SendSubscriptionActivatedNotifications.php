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

        $response = $this->smsService->sendTemplate(
            phone: $user->mobile,
            templateSlug: 'subscription-status',
            variables: [
                'status' => 'activated',
                'plan' => (string) ($subscription->stage?->name ?? 'Membership'),
                'reference' => $this->subscriptionReference($subscription),
                'app_name' => (string) config('app.name'),
            ],
            type: 'transactional',
            userId: $user->id,
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

    private function subscriptionReference(UserSubscription $subscription): string
    {
        $compact = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $subscription->uuid), 0, 10));

        return $compact !== '' ? "SUB-{$compact}" : 'SUB-NA';
    }
}

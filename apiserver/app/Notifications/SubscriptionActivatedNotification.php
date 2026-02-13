<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Membership\UserSubscription;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Notification triggered when a membership subscription becomes active.
 *
 * Dispatches email/push/database channels and supplies contextual payload
 * data for the frontend Filament dashboard.
 */
final class SubscriptionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly UserSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return array_values(array_unique($channels));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->getTitle())
            ->greeting("Hi {$notifiable->name},")
            ->line($this->getMessage())
            ->action('Visit Dashboard', $this->getActionUrl())
            ->line('Thank you for staying active with '.config('app.name').'.');
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->getTitle())
            ->body($this->getMessage())
            ->icon('/icon-192x192.png')
            ->badge('/badge-72x72.png')
            ->data(['url' => $this->getActionUrl()])
            ->options(['TTL' => 3600]);
    }

    public function toDatabase(object $notifiable): array
    {
        $notification = FilamentNotification::make()
            ->title($this->getTitle())
            ->success()
            ->icon('heroicon-o-check-badge')
            ->body($this->getMessage())
            ->actions([
                Action::make('view-dashboard')
                    ->label('Visit Dashboard')
                    ->url($this->getActionUrl()),
            ]);

        return $notification->getDatabaseMessage();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_activated',
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'stage' => $this->subscription->stage?->name,
            'level' => $this->subscription->currentLevel?->full_name,
            'action_url' => $this->getActionUrl(),
            'icon' => 'heroicon-o-check-badge',
        ];
    }

    private function getTitle(): string
    {
        $stage = $this->subscription->stage?->name ?? 'membership';

        return "Subscription Activated: {$stage}";
    }

    private function getMessage(): string
    {
        $stage = $this->subscription->stage?->name ?? 'membership';
        $level = $this->subscription->currentLevel?->full_name ?? $stage;

        return "Your {$stage} subscription ({$level}) is now active. Explore the latest deals and build your earnings network.";
    }

    private function getActionUrl(): string
    {
        $baseUrl = config('app.client_url', 'http://localhost:3000');

        return rtrim($baseUrl, '/').'/dashboard';
    }
}

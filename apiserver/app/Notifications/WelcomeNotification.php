<?php

declare(strict_types=1);

namespace App\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly bool $sendEmail = true,
        private readonly bool $sendPush = true,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Add email channel if user has email and email sending is enabled
        if ($this->sendEmail && $notifiable->email) {
            $channels[] = 'mail';
        }

        // Add WebPush channel if user has push subscriptions and push is enabled
        if ($this->sendPush && method_exists($notifiable, 'pushSubscriptions')) {
            if ($notifiable->pushSubscriptions()->exists()) {
                $channels[] = WebPushChannel::class;
            }
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $clientUrl = config('app.client_url', config('app.url'));

        return (new MailMessage)
            ->subject("Welcome to {$appName} - Shop, Earn & Grow!")
            ->greeting("Hi {$notifiable->name},")
            ->line("We're excited to have you on board with {$appName} — your one-stop destination for amazing products and an opportunity to build your own earning network!")
            ->line("Here's what you can start doing right now:")
            ->line('- Explore a wide range of quality products at member-exclusive prices')
            ->line('- Earn commissions when your network shops')
            ->line('- Grow your team and unlock higher rewards')
            ->line('- Enjoy special promotions, offers, and early-bird deals')
            ->action('Start Shopping & Earning', $clientUrl)
            ->line('Need help getting started? Our friendly support team is always here to guide you.')
            ->salutation("Warm regards,\nTeam {$appName}");
    }

    /**
     * Get the WebPush representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $appName = config('app.name');

        return (new WebPushMessage)
            ->title("Welcome to {$appName}!")
            ->icon('/icon-192x192.png')
            ->body("Hi {$notifiable->name}! Your account is ready. Start exploring now!")
            ->action('Get Started', 'open_app')
            ->data(['url' => config('app.client_url', config('app.url'))])
            ->badge('/badge-72x72.png')
            ->options(['TTL' => 3600]);
    }

    /**
     * Get the database representation of the notification.
     * Compatible with both Filament admin and API frontend.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $appName = config('app.name');

        // Use Filament's notification format for compatibility
        return FilamentNotification::make()
            ->title("Welcome to {$appName}!")
            ->success()
            ->icon('heroicon-o-sparkles')
            ->body("Hi {$notifiable->name}! Your account has been created successfully. Start exploring amazing products and earning opportunities.")
            ->actions([
                \Filament\Actions\Action::make('get_started')
                    ->label('Get Started')
                    ->url(config('app.client_url', config('app.url'))),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification (for non-database channels).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to '.config('app.name'),
            'message' => "Hi {$notifiable->name}! Your account has been created successfully.",
            'body' => "Hi {$notifiable->name}! Your account has been created successfully. Start exploring amazing products and earning opportunities.",
            'icon' => 'heroicon-o-sparkles',
            'action_url' => config('app.client_url', config('app.url')),
            'action_text' => 'Get Started',
        ];
    }
}

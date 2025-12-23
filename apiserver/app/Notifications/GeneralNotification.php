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

/**
 * General purpose notification that can be sent via multiple channels.
 * Use this for alerts, updates, reminders, etc.
 * Compatible with Filament admin panel notifications.
 */
class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private const TYPE_ICON_MAP = [
        'info' => 'heroicon-o-information-circle',
        'success' => 'heroicon-o-check-circle',
        'warning' => 'heroicon-o-exclamation-triangle',
        'alert' => 'heroicon-o-exclamation-circle',
        'general' => 'heroicon-o-bell',
    ];

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly ?string $actionUrl = null,
        private readonly ?string $actionText = null,
        private readonly array $channels = ['database'],
        private readonly string $type = 'general',
        private readonly string $icon = '/icon-192x192.png',
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $resolvedChannels = [];

        foreach ($this->channels as $channel) {
            if ($channel === 'mail' && $notifiable->email) {
                $resolvedChannels[] = 'mail';
            } elseif ($channel === 'push' && method_exists($notifiable, 'pushSubscriptions')) {
                if ($notifiable->pushSubscriptions()->exists()) {
                    $resolvedChannels[] = WebPushChannel::class;
                }
            } elseif ($channel === 'database') {
                $resolvedChannels[] = 'database';
            }
        }

        return $resolvedChannels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting("Hi {$notifiable->name},")
            ->line($this->message);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        return $mail->salutation('Regards,'."\n".'Team '.config('app.name'));
    }

    /**
     * Get the WebPush representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $push = (new WebPushMessage)
            ->title($this->title)
            ->icon($this->icon)
            ->body($this->message)
            ->badge('/badge-72x72.png')
            ->options(['TTL' => 3600]);

        if ($this->actionUrl) {
            $push->data(['url' => $this->actionUrl]);
            if ($this->actionText) {
                $push->action($this->actionText, 'open_url');
            }
        }

        return $push;
    }

    /**
     * Get the database representation of the notification.
     * Compatible with Filament admin panel.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $notification = FilamentNotification::make()
            ->title($this->title)
            ->icon(self::TYPE_ICON_MAP[$this->type] ?? 'heroicon-o-bell')
            ->body($this->message);

        // Apply color based on type
        match ($this->type) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'alert' => $notification->danger(),
            'info' => $notification->info(),
            default => null,
        };

        // Add action if URL is provided
        if ($this->actionUrl) {
            $notification->actions([
                \Filament\Actions\Action::make('view')
                    ->label($this->actionText ?? 'View')
                    ->url($this->actionUrl),
            ]);
        }

        return $notification->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification (for non-database channels).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'body' => $this->message,
            'icon' => self::TYPE_ICON_MAP[$this->type] ?? 'heroicon-o-bell',
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
    }

    /**
     * Static factory methods for common notification types
     */
    public static function info(string $title, string $message, ?string $actionUrl = null): self
    {
        return new self(
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionText: $actionUrl ? 'View Details' : null,
            channels: ['database', 'push'],
            type: 'info',
        );
    }

    public static function success(string $title, string $message, ?string $actionUrl = null): self
    {
        return new self(
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionText: $actionUrl ? 'View' : null,
            channels: ['database', 'push'],
            type: 'success',
        );
    }

    public static function warning(string $title, string $message, ?string $actionUrl = null): self
    {
        return new self(
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionText: $actionUrl ? 'Take Action' : null,
            channels: ['database', 'push', 'mail'],
            type: 'warning',
        );
    }

    public static function alert(string $title, string $message, ?string $actionUrl = null): self
    {
        return new self(
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionText: $actionUrl ? 'View' : null,
            channels: ['database', 'push', 'mail'],
            type: 'alert',
        );
    }
}

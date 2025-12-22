<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PushNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $icon;
    protected $url;

    public function __construct(
        string $title,
        string $body,
        string $icon = '/icon-192x192.png',
        ?string $url = null
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->icon = $icon;
        $this->url = $url ?? config('app.url');
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->icon($this->icon)
            ->body($this->body)
            ->action('View', 'notification_action')
            ->data(['url' => $this->url])
            ->badge('/badge-72x72.png')
            ->options(['TTL' => 3600]);
    }
}

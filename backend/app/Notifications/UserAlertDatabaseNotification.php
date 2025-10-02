<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class UserAlertDatabaseNotification extends Notification
{
    use Queueable;

    protected Model $record;
    protected string $message;
    protected string $level = 'normal';

    /**
     * Create a new notification instance.
     */
    public function __construct(Model $record, string $msg, string $level = 'normal')
    {
        $this->record = $record;
        $this->message = $msg;

        if (in_array($level, ['normal', 'success', 'warning', 'danger'])) {
            $this->level = $level;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // database only
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'record_type' => get_class($this->record),
            'record_id'   => $this->record->getKey(),
            'message'     => $this->message,
            'level'       => $this->level,
        ];
    }
}

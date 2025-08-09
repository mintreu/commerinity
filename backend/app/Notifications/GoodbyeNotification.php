<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoodbyeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your " . config('app.name') . " Account Has Been Deleted")
            ->greeting("Hello {$notifiable->name},")
            ->line("We confirm that your account with " . config('app.name') . " has been successfully deleted as per your request.")
            ->line("All your personal data and account information have been removed from our systems in compliance with our privacy policy.")
            ->line("If this deletion was not initiated by you, please contact our support team immediately.")
            ->action('Contact Support', url('/support'))
            ->line("We’re sorry to see you go, and we thank you for the time you spent with us.")
            ->salutation('Best regards,
Team ' . config('app.name'));
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

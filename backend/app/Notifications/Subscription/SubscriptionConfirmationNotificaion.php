<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionConfirmationNotificaion extends Notification
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
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🎉 Your Subscription is Confirmed – Welcome to " . config('app.name') . "!")
            ->greeting("Hi {$notifiable->name},")
            ->line("We’re thrilled to let you know that your subscription is now **successfully confirmed**. 🚀")
            ->line("From today, you are officially part of " . config('app.name') . " as a subscribed member.")
            ->line("Here’s what you can start enjoying right away:")
            ->line('- 🛒 Shop at member-only prices')
            ->line('- 💰 Earn rewards and commissions')
            ->line('- 📈 Grow your network and unlock higher tiers')
            ->line('- 🎁 Access exclusive promotions and offers')
            ->action('Start Exploring Now', config('app.client_url'))
            ->line("If you need help, reach us anytime at 📩 support@vvin.in")
            ->salutation("Warm regards,\nTeam " . config('app.name'));
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

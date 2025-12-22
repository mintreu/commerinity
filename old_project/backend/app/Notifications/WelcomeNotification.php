<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
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
            ->subject("Welcome to " . config('app.name') . " – Shop, Earn & Grow!")
            ->greeting("Hi {$notifiable->name},")
            ->line("We’re excited to have you on board with " . config('app.name') . " — your one-stop destination for amazing products **and** an opportunity to build your own earning network!")
            ->line("Here’s what you can start doing right now:")
            ->line('- 🛒 Explore a wide range of quality products at member-exclusive prices')
            ->line('- 💰 Earn commissions when your network shops')
            ->line('- 📈 Grow your team and unlock higher rewards')
            ->line('- 🎁 Enjoy special promotions, offers, and early-bird deals')
            ->line('- 📢 Stay updated with the latest launches and campaigns')
            ->action('Start Shopping & Earning', config('app.client_url'))
            ->line("Need help getting started? Our friendly support team is always here to guide you.")
            ->line('📩 support@vvin.in')
            ->line("Let’s make your journey rewarding — both in savings and earnings!")
            ->salutation('Warm regards,
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

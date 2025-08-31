<?php

namespace App\Notifications\User;

use App\Casts\AuthStatusCast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserStatusChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match ($notifiable->status) {
            AuthStatusCast::SUBSCRIBED   => $this->getSubscribedStatusNotification($notifiable),
            AuthStatusCast::ACTIVE       => $this->getActiveStatusNotification($notifiable),
            AuthStatusCast::SUSPENDED    => $this->getSuspendedStatusNotification($notifiable),
            AuthStatusCast::INACTIVE     => $this->getInactiveStatusNotification($notifiable),
            AuthStatusCast::BANNED       => $this->getBannedStatusNotification($notifiable),
            AuthStatusCast::UNSUBSCRIBED => $this->getUnsubscribedStatusNotification($notifiable),
            default => $this->getGenericStatusNotification($notifiable),
        };
    }

    /**
     * Notification for SUBSCRIBED status.
     */
    protected function getSubscribedStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("✅ Membership Activated – You are now a {$notifiable->level->name} Member!")
            ->greeting("Congratulations {$notifiable->name},")
            ->line("Your account has been successfully upgraded. 🎉")
            ->line("You are now a **{$notifiable->level->name} Member** of " . config('app.name') . ".")
            ->line("As a valued member, you can now enjoy all the benefits and privileges that come with your membership level.")
            ->line('- Exclusive offers and deals')
            ->line('- Commission earning opportunities')
            ->line('- Priority support and updates')
            ->action('Check Member Dashboard', config('app.client_url') . '/dashboard')
            ->line("Enjoy your journey with us — shop, earn & grow with " . config('app.name') . "!")
            ->salutation("Warm regards,\nTeam " . config('app.name'));
    }

    /**
     * Notification for ACTIVE status.
     */
    protected function getActiveStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🎉 Welcome Back – Your Account is Active!")
            ->greeting("Hi {$notifiable->name},")
            ->line("Good news! Your account at " . config('app.name') . " is now **Active**.")
            ->line("You can now log in and continue enjoying all our features.")
            ->action('Go to Dashboard', config('app.client_url') . '/dashboard')
            ->salutation("Cheers,\nTeam " . config('app.name'));
    }

    /**
     * Notification for SUSPENDED status.
     */
    protected function getSuspendedStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Account Suspended")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your account has been temporarily **suspended**.")
            ->line("If you believe this is a mistake, please contact support.")
            ->line("📩 support@vvin.in")
            ->salutation("Regards,\nTeam " . config('app.name'));
    }

    /**
     * Notification for INACTIVE status.
     */
    protected function getInactiveStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("ℹ️ Account Inactive")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your account is currently **inactive**. To reactivate, please log in or contact our support team.")
            ->action('Reactivate Account', config('app.client_url') . '/login')
            ->salutation("Team " . config('app.name'));
    }

    /**
     * Notification for BANNED status.
     */
    protected function getBannedStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⛔ Account Banned")
            ->greeting("Hi {$notifiable->name},")
            ->line("Unfortunately, your account has been **banned** due to violations of our policies.")
            ->line("If you’d like to appeal, contact us at 📩 support@vvin.in")
            ->salutation("Team " . config('app.name'));
    }

    /**
     * Notification for UNSUBSCRIBED status.
     */
    protected function getUnsubscribedStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("📢 You’ve Unsubscribed from Membership")
            ->greeting("Hi {$notifiable->name},")
            ->line("You’ve successfully **unsubscribed** from your membership.")
            ->line("You can re-subscribe anytime to continue enjoying exclusive member benefits.")
            ->action('Subscribe Again', config('app.client_url') . '/subscribe')
            ->salutation("Team " . config('app.name'));
    }

    /**
     * Generic fallback notification.
     */
    protected function getGenericStatusNotification(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("ℹ️ Account Status Updated")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your account status has been updated to: **{$notifiable->status->getLabel()}**.")
            ->salutation("Team " . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}

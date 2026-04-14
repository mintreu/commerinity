<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\WhatsAppMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

final class JobApplicationWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $password,
        public readonly bool $isVerified,
    ) {}

    public function via(User $notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        // Add WhatsApp if mobile exists and app supports it
        if (! empty($notifiable->mobile) && class_exists(WhatsAppMessage::class)) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    public function toMail(User $notifiable): MailMessage
    {
        $loginUrl = config('app.url');

        return (new MailMessage)
            ->subject('Welcome to '.config('app.name').' - Your Account is Ready!')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Welcome to '.config('app.name').'! Your account has been successfully created.')
            ->line('Here are your login credentials:')
            ->line('**Username:** '.$notifiable->email)
            ->line('**Password:** '.$this->password)
            ->line('')
            ->line('**Your password is:** Last 6 digits of your mobile number OR birth month+year (MMYYYY)')
            ->line('')
            ->line('**Important: Please change your password after your first login. Never share your credentials with anyone.**')
            ->line($this->isVerified ? '' : '**Action Required:** Please verify your email address to access all features.')
            ->line('')
            ->action('Login Now', $loginUrl)
            ->line('Need help? Contact our support team anytime.')
            ->salutation('Best regards,');
    }

    public function toWhatsApp(User $notifiable): WhatsAppMessage
    {
        $loginUrl = config('app.url');

        return WhatsAppMessage::create()
            ->to($notifiable->mobile)
            ->line('ð Welcome to '.config('app.name').'!')
            ->line('Hello '.$notifiable->name.',')
            ->line('Your account is ready!')
            ->line('Email: '.$notifiable->email)
            ->line('Password: '.$this->password)
            ->line('')
            ->line('ð Login: '.$loginUrl)
            ->line('Password = Last 6 digits of this mobile OR MMYYYY from DOB')
            ->line('Please change password after first login!');
    }

    public function toWebPush(User $notifiable, $notification): WebPushMessage
    {
        $loginUrl = config('app.url');

        return (new WebPushMessage)
            ->title('Welcome to '.config('app.name'))
            ->icon('/icon-192x192.png')
            ->badge('/badge-72x72.png')
            ->body('Your account is ready. Login to get started.')
            ->data(['url' => $loginUrl])
            ->action('Login Now', 'open_url')
            ->options(['TTL' => 3600]);
    }

    public function toArray(User $notifiable): array
    {
        return [
            'password' => $this->password,
            'is_verified' => $this->isVerified,
            'message' => 'Account created via bulk job application import',
        ];
    }
}

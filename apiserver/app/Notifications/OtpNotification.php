<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $otp,
        private readonly string $purpose = 'verification',
    ) {}

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
        $appName = config('app.name');
        $purposeText = match ($this->purpose) {
            'registration' => 'complete your registration',
            'login' => 'login to your account',
            'password_reset' => 'reset your password',
            default => 'verify your identity',
        };

        $name = $notifiable->name ? ' '.$notifiable->name : '';

        return (new MailMessage)
            ->subject("{$appName} - Your OTP Code")
            ->greeting("Hello{$name},")
            ->line("You requested an OTP to {$purposeText}.")
            ->line("**Your OTP: {$this->otp}**")
            ->line('This OTP is valid for 10 minutes.')
            ->line("If you didn't request this code, please ignore this email.")
            ->salutation("Regards,\nTeam {$appName}");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'otp',
            'purpose' => $this->purpose,
        ];
    }
}

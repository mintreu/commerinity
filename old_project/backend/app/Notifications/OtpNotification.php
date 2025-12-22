<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $otp;

    public function __construct(int $otp)
    {
        $this->otp = $otp;
    }

    public function via(object $notifiable): array
    {
        return ['mail']; // You can also add 'database', 'sms' etc. later
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name') . ' - Your OTP Code')
            ->line('Don\'t share this code with anyone.')
            ->line('Your OTP is: **' . $this->otp . '**')
            ->line('This OTP is valid for 10 minutes.');
    }
}

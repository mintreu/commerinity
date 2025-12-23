<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $purpose = 'verification',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' - Your OTP Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'purpose' => $this->purpose,
                'purposeText' => $this->getPurposeText(),
                'appName' => config('app.name'),
            ],
        );
    }

    private function getPurposeText(): string
    {
        return match ($this->purpose) {
            'registration' => 'complete your registration',
            'login' => 'login to your account',
            'password_reset' => 'reset your password',
            default => 'verify your identity',
        };
    }
}

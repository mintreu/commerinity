<?php

declare(strict_types=1);

namespace App\Services\Recruitment;

use App\Contracts\Services\NotificationSmsSenderInterface;
use App\Models\Recruitment\JobApplication;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Log;

final class JobApplicationNotificationService
{
    public function __construct(
        private readonly NotificationSmsSenderInterface $smsService,
    ) {}

    public function notifyApplied(User $user, JobApplication $application, bool $requiresPayment): void
    {
        $applicationNumber = $application->uuid;
        $jobTitle = $application->recruitment?->title ?? 'the selected role';
        $applicationUrl = rtrim((string) config('app.client_url'), '/')."/career/applications/{$applicationNumber}";

        $title = $requiresPayment
            ? 'Job Application Created'
            : 'Job Application Submitted';

        $message = $requiresPayment
            ? "Your application for {$jobTitle} has been created and is awaiting payment. Application No: {$applicationNumber}."
            : "Your application for {$jobTitle} has been submitted successfully. Application No: {$applicationNumber}.";

        $user->notify(new GeneralNotification(
            title: $title,
            message: $message,
            actionUrl: $applicationUrl,
            actionText: 'View Application',
            channels: ['database', 'mail', 'push'],
            type: 'info',
        ));

        $this->sendSms(
            $user,
            $requiresPayment
                ? "Application No {$applicationNumber} created for {$jobTitle}. Please complete payment to submit."
                : "Application No {$applicationNumber} submitted successfully for {$jobTitle}.",
        );
    }

    public function notifyPaymentConfirmed(User $user, JobApplication $application): void
    {
        $applicationNumber = $application->uuid;
        $jobTitle = $application->recruitment?->title ?? 'the selected role';
        $applicationUrl = rtrim((string) config('app.client_url'), '/')."/career/applications/{$applicationNumber}";

        $user->notify(new GeneralNotification(
            title: 'Application Payment Confirmed',
            message: "Payment received and your application is now submitted. Application No: {$applicationNumber}.",
            actionUrl: $applicationUrl,
            actionText: 'Track Application',
            channels: ['database', 'mail', 'push'],
            type: 'success',
        ));

        $this->sendSms(
            $user,
            "Payment confirmed for {$jobTitle}. Application No {$applicationNumber} is submitted.",
        );
    }

    private function sendSms(User $user, string $message): void
    {
        if (! $user->mobile) {
            return;
        }

        if (! $this->smsService->canSend(1)) {
            Log::info('Job application SMS skipped: insufficient balance', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $response = $this->smsService->sendSingle(
            phone: $user->mobile,
            message: $message,
            type: 'transactional',
            userId: $user->id,
        );

        if (! $response->success) {
            Log::warning('Job application SMS failed', [
                'user_id' => $user->id,
                'error' => $response->errorMessage,
            ]);
        }
    }
}


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
            'job-application-received',
            [
                'name' => (string) ($user->name ?? 'Applicant'),
                'application_id' => (string) $applicationNumber,
                'app_name' => (string) config('app.name'),
            ],
        );
    }

    public function notifyPaymentConfirmed(User $user, JobApplication $application): void
    {
        $applicationNumber = $application->uuid;
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
            'job-application-received',
            [
                'name' => (string) ($user->name ?? 'Applicant'),
                'application_id' => (string) $applicationNumber,
                'app_name' => (string) config('app.name'),
            ],
        );
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function sendSms(User $user, string $templateSlug, array $variables): void
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

        $response = $this->smsService->sendTemplate(
            phone: $user->mobile,
            templateSlug: $templateSlug,
            variables: $variables,
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

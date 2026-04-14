<?php

declare(strict_types=1);

namespace App\Observers;

use App\Casts\KycStatusCast;
use App\Models\Kyc;
use App\Models\User;
use App\Notifications\GeneralNotification;

final class KycObserver
{
    public function updated(Kyc $kyc): void
    {
        if (! $kyc->wasChanged('status')) {
            return;
        }

        $user = $kyc->kycable;
        if (! $user instanceof User) {
            return;
        }

        $status = $kyc->status instanceof KycStatusCast
            ? $kyc->status
            : KycStatusCast::tryFrom((string) $kyc->status);

        if (! $status) {
            return;
        }

        $profileUrl = rtrim((string) config('app.client_url'), '/').'/profile/kyc';

        if ($status === KycStatusCast::APPROVED) {
            $user->notify(new GeneralNotification(
                title: 'KYC Approved',
                message: 'Your KYC has been approved. You can now access all eligible features.',
                actionUrl: $profileUrl,
                actionText: 'View KYC',
                channels: ['database', 'push', 'mail'],
                type: 'success',
            ));

            return;
        }

        if ($status === KycStatusCast::REJECTED) {
            $reason = trim((string) ($kyc->rejection_reason ?? ''));
            $message = 'Your KYC was rejected. Please review details and resubmit.';
            if ($reason !== '') {
                $message .= ' Reason: '.$reason;
            }

            $user->notify(new GeneralNotification(
                title: 'KYC Rejected',
                message: $message,
                actionUrl: $profileUrl,
                actionText: 'Resubmit KYC',
                channels: ['database', 'push', 'mail'],
                type: 'warning',
            ));
        }
    }
}


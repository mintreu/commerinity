<?php

declare(strict_types=1);

namespace App\Listeners\Notification;

use App\Casts\CommissionTypeCast;
use App\Events\Affiliate\CommissionProcessed;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\MoneyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendCommissionProcessedNotification implements ShouldQueue
{
    use Queueable;

    public function handle(CommissionProcessed $event): void
    {
        $commission = $event->commission->loadMissing(['user', 'fromUser']);
        $recipient = $commission->user;

        if (! $recipient instanceof User) {
            return;
        }

        $typeLabel = $commission->type instanceof CommissionTypeCast
            ? $commission->type->getLabel()
            : (string) $commission->type;

        $amount = MoneyService::format($commission->net_amount);
        $fromName = $commission->fromUser?->name ?: 'your network';
        $ref = $commission->uuid ?: ('COM-'.$commission->id);

        $recipient->notify(new GeneralNotification(
            title: 'Commission Credited',
            message: "{$amount} commission added for {$typeLabel} from {$fromName}. Ref: {$ref}.",
            actionUrl: rtrim((string) config('app.client_url'), '/').'/wallet',
            actionText: 'View Wallet',
            channels: ['database', 'push', 'mail'],
            type: 'success',
        ));
    }
}


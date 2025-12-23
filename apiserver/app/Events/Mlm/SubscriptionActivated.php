<?php

declare(strict_types=1);

namespace App\Events\Mlm;

use App\Models\Membership\UserSubscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Event dispatched when a subscription is activated.
 *
 * Listeners can use this to:
 * - Send welcome notifications
 * - Update dashboards
 * - Trigger additional workflows
 */
class SubscriptionActivated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly UserSubscription $subscription,
        public readonly Collection $commissions,
    ) {}
}

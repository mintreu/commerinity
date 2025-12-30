<?php

declare(strict_types=1);

namespace App\Events\Affiliate;

use App\Contracts\Affiliate\CommissionTrigger;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a commission trigger occurs
 *
 * This event is dispatched immediately and triggers async processing.
 * Listeners can calculate commissions in the background.
 */
final class CommissionTriggered implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly CommissionTrigger $trigger,
        public readonly bool $processAsync = true,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $userId = $this->trigger->getTriggeringUserId();

        return [
            new PrivateChannel("user.{$userId}.commissions"),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'trigger_type' => $this->trigger->getTriggerType(),
            'trigger_id' => $this->trigger->getId(),
            'user_id' => $this->trigger->getTriggeringUserId(),
            'amount' => $this->trigger->getCommissionableAmount(),
            'status' => 'triggered',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'commission.triggered';
    }
}

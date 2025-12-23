<?php

declare(strict_types=1);

namespace App\Events\Mlm;

use App\Models\Mlm\MlmCommission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired after a commission is persisted to database
 *
 * This event broadcasts to the recipient's channel for real-time UI updates.
 */
final class CommissionProcessed implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly MlmCommission $commission,
        public readonly bool $isNew = true,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->commission->user_id}.commissions"),
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
            'commission_id' => $this->commission->id,
            'commission_uuid' => $this->commission->uuid,
            'type' => $this->commission->type,
            'gross_amount' => $this->commission->gross_amount,
            'net_amount' => $this->commission->net_amount,
            'description' => $this->commission->description,
            'status' => $this->commission->status,
            'is_new' => $this->isNew,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'commission.processed';
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        // Only broadcast if commission has a valid recipient
        return $this->commission->user_id > 0;
    }
}

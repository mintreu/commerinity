<?php

declare(strict_types=1);

namespace App\Events\Mlm;

use App\Contracts\Mlm\CommissionTrigger;
use App\Dto\Mlm\CommissionResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Event fired after all commissions are calculated for a trigger
 *
 * Contains all commission results before persistence.
 * Listeners can modify/validate results before saving.
 */
final class CommissionsCalculated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  Collection<int, CommissionResult>  $results
     */
    public function __construct(
        public readonly CommissionTrigger $trigger,
        public readonly Collection $results,
        public readonly array $metadata = [],
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("user.{$this->trigger->getTriggeringUserId()}.commissions"),
        ];

        // Also broadcast to each recipient
        foreach ($this->results as $result) {
            $channels[] = new PrivateChannel("user.{$result->recipientId}.commissions");
        }

        return array_unique($channels, SORT_REGULAR);
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
            'total_commissions' => $this->results->count(),
            'total_amount' => $this->results->sum('grossAmount'),
            'status' => 'calculated',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'commission.calculated';
    }

    /**
     * Get total gross amount of all commissions
     */
    public function getTotalGrossAmount(): int
    {
        return $this->results->sum('grossAmount');
    }

    /**
     * Get commissions by type
     *
     * @return Collection<string, Collection<int, CommissionResult>>
     */
    public function getResultsByType(): Collection
    {
        return $this->results->groupBy('type');
    }

    /**
     * Get commissions for a specific recipient
     *
     * @return Collection<int, CommissionResult>
     */
    public function getResultsForRecipient(int $recipientId): Collection
    {
        return $this->results->where('recipientId', $recipientId);
    }
}

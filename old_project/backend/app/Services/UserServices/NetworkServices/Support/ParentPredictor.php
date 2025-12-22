<?php

namespace App\Services\UserServices\NetworkServices\Support;

use App\Casts\AuthStatusCast;
use App\Models\User;

class ParentPredictor
{
    protected User $record;
    protected ?User $sponsor = null;
    protected bool $sponsorAllowed = false;

    public function __construct(User $record, ?User $sponsor)
    {
        $this->record = $record;
        $this->sponsor = $this->findSponsorWithOpenSlot($sponsor);
    }

    public static function make(User $newMember, ?User $sponsor):static
    {
        return new static($newMember, $sponsor);
    }

    public function getSponsor(): ?User
    {
        return $this->sponsor;
    }


    /**
     * First check the sponsor, then all descendants, for an open slot.
     */
    protected function findSponsorWithOpenSlot(User $sponsor): ?User
    {
        // Step 1: Quick check on the sponsor
        $sponsor->loadCount([
            'children as subscribed_children_count' => function ($q) {
                $q->where('status', AuthStatusCast::SUBSCRIBED);
            }
        ]);

        if ($sponsor->subscribed_children_count <= config('app.matrix')) {
            return $sponsor;
        }

        // Step 2: Directly get the first descendant with space
        return $this->getSubscribedDescendantsWithCounts($sponsor)->first();
    }


    /**
     * Retrieve all subscribed descendants with their subscribed children count.
     */
    protected function getSubscribedDescendantsWithCounts(User $sponsor)
    {
        return User::whereIn('id', $this->getDescendantIds($sponsor))
            ->withCount([
                'children as subscribed_children_count' => function ($q) {
                    $q->where('status', AuthStatusCast::SUBSCRIBED);
                }
            ])
            ->having('subscribed_children_count', '<=', config('app.matrix'))
            ->orderBy('id') // optional: order by something meaningful
            ->get();
    }


    /**
     * Get IDs of all subscribed descendants for the given sponsor.
     * Replace with your preferred "descendants" query if using a nested set or closure table.
     */
    protected function getDescendantIds(User $sponsor): array
    {
        return $sponsor->descendants() // assumes you have a descendants() relationship from a tree package
        ->where('status', AuthStatusCast::SUBSCRIBED)
            ->pluck('id')
            ->all();
    }
}

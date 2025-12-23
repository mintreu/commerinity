<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Helpdesk\Helpdesk;
use App\Models\Helpdesk\HelpdeskConversation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasHelpdeskTickets
{
    public function tickets(): MorphMany
    {
        return $this->morphMany(Helpdesk::class, 'authorable');
    }

    public function ticketConversations(): MorphMany
    {
        return $this->morphMany(HelpdeskConversation::class, 'authorable');
    }

    public function activeTickets(): MorphMany
    {
        return $this->tickets()->active();
    }

    public function hasActiveTickets(): bool
    {
        return $this->activeTickets()->exists();
    }

    public function getActiveTicketsCountAttribute(): int
    {
        return $this->activeTickets()->count();
    }

    public function getTotalTicketsCountAttribute(): int
    {
        return $this->tickets()->count();
    }
}

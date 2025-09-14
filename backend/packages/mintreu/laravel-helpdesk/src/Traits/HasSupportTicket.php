<?php

namespace Mintreu\LaravelHelpdesk\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mintreu\LaravelHelpdesk\Models\Helpdesk;
use Mintreu\LaravelHelpdesk\Models\HelpdeskConversation;

trait HasSupportTicket
{
    /**
     * All tickets created by this authorable model.
     */
    public function tickets(): MorphMany
    {
        return $this->morphMany(Helpdesk::class, 'authorable');
    }

    /**
     * All helpdesk conversations authored by this model (across all tickets).
     */
    public function ticketConversations(): MorphMany
    {
        return $this->morphMany(HelpdeskConversation::class, 'authorable');
    }

    /**
     * Conversations authored by this model for a specific ticket.
     *
     * Usage:
     * $user->ticketConversationsFor($ticket)->get();
     * $user->ticketConversationsFor($ticketId)->latest()->first();
     */
    public function ticketConversationsFor(Helpdesk|int $ticket): HasMany
    {
        $ticketId = $ticket instanceof Helpdesk ? $ticket->getKey() : $ticket;

        // Filter the user's authored conversations by a specific helpdesk_id
        return $this->ticketConversations()->where('helpdesk_id', $ticketId);
    }
}

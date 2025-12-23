<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class HelpdeskConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'message',
        'authorable_type',
        'authorable_id',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'attachment' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForTicket($query, int $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function isFromUser(): bool
    {
        return $this->authorable_type === User::class;
    }

    public function isFromAdmin(): bool
    {
        return $this->authorable_type === Admin::class;
    }
}

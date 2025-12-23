<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\TicketPriorityCast;
use App\Casts\TicketStatusCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'priority',
        'topic_id',
        'user_id',
        'status',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriorityCast::class,
            'status' => TicketStatusCast::class,
            'attachment' => 'array',
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (Ticket $ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }

            if (empty($ticket->status)) {
                $ticket->status = TicketStatusCast::OPEN;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTopic::class, 'topic_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(HelpdeskConversation::class, 'ticket_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

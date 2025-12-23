<?php

declare(strict_types=1);

namespace App\Models\Helpdesk;

use App\Casts\HelpdeskPriorityCast;
use App\Casts\HelpdeskStatusCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Helpdesk extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected static function newFactory(): \Database\Factories\HelpdeskFactory
    {
        return \Database\Factories\HelpdeskFactory::new();
    }

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'priority',
        'status',
        'topic_id',
        'authorable_type',
        'authorable_id',
        'assigned_to',
        'chatbot_session_id',
        'chatbot_context',
        'resolved_at',
        'closed_at',
        'last_activity_at',
        'satisfaction_rating',
        'satisfaction_feedback',
    ];

    protected function casts(): array
    {
        return [
            'priority' => HelpdeskPriorityCast::class,
            'status' => HelpdeskStatusCast::class,
            'chatbot_context' => 'array',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'satisfaction_rating' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Helpdesk $ticket) {
            if (! $ticket->uuid) {
                $prefix = config('helpdesk.ticket.prefix', 'TICKET');
                $ticket->uuid = $prefix.'-'.now()->format('ymd').'-'.strtoupper(Str::random(8));
            }

            if (! $ticket->status) {
                $ticket->status = HelpdeskStatusCast::Open;
            }

            if (! $ticket->priority) {
                $ticket->priority = HelpdeskPriorityCast::getDefault();
            }

            $ticket->last_activity_at = now();
        });

        static::updating(function (Helpdesk $ticket) {
            $ticket->last_activity_at = now();

            if ($ticket->isDirty('status')) {
                if ($ticket->status === HelpdeskStatusCast::Resolved && ! $ticket->resolved_at) {
                    $ticket->resolved_at = now();
                }

                if ($ticket->status === HelpdeskStatusCast::Closed && ! $ticket->closed_at) {
                    $ticket->closed_at = now();
                }
            }
        });
    }

    // ========================================
    // Media Collections
    // ========================================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ticketAttachment')
            ->useDisk('private');
    }

    // ========================================
    // Relationships
    // ========================================

    public function topic(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTopic::class, 'topic_id');
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(HelpdeskConversation::class, 'helpdesk_id');
    }

    // ========================================
    // Accessors
    // ========================================

    public function getIsActiveAttribute(): bool
    {
        return $this->status->isActive();
    }

    public function getCanReplyAttribute(): bool
    {
        return $this->status->canReply();
    }

    public function getCanCloseAttribute(): bool
    {
        return $this->status->canClose();
    }

    public function getCanReopenAttribute(): bool
    {
        return $this->status->canReopen();
    }

    public function getConversationsCountAttribute(): int
    {
        return $this->conversations()->count();
    }

    public function getIsFromChatbotAttribute(): bool
    {
        return $this->chatbot_session_id !== null;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', HelpdeskStatusCast::Open);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            HelpdeskStatusCast::Open,
            HelpdeskStatusCast::AwaitingReply,
            HelpdeskStatusCast::InProgress,
        ]);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', HelpdeskStatusCast::Resolved);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', HelpdeskStatusCast::Closed);
    }

    public function scopeByPriority(Builder $query, HelpdeskPriorityCast $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', HelpdeskPriorityCast::Urgent);
    }

    public function scopeFromChatbot(Builder $query): Builder
    {
        return $query->whereNotNull('chatbot_session_id');
    }

    public function scopeForUser(Builder $query, Model $user): Builder
    {
        return $query->where('authorable_type', $user->getMorphClass())
            ->where('authorable_id', $user->getKey());
    }

    // ========================================
    // Actions
    // ========================================

    public function markAsInProgress(): self
    {
        $this->update(['status' => HelpdeskStatusCast::InProgress]);

        return $this;
    }

    public function markAsAwaitingReply(): self
    {
        $this->update(['status' => HelpdeskStatusCast::AwaitingReply]);

        return $this;
    }

    public function resolve(?string $feedback = null): self
    {
        $this->update([
            'status' => HelpdeskStatusCast::Resolved,
            'resolved_at' => now(),
        ]);

        return $this;
    }

    public function close(?string $feedback = null): self
    {
        $this->update([
            'status' => HelpdeskStatusCast::Closed,
            'closed_at' => now(),
        ]);

        return $this;
    }

    public function reopen(): self
    {
        $this->update([
            'status' => HelpdeskStatusCast::Open,
            'resolved_at' => null,
            'closed_at' => null,
        ]);

        return $this;
    }

    public function rateSatisfaction(int $rating, ?string $feedback = null): self
    {
        $this->update([
            'satisfaction_rating' => min(5, max(1, $rating)),
            'satisfaction_feedback' => $feedback,
        ]);

        return $this;
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

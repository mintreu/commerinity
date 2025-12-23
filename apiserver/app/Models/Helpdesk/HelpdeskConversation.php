<?php

declare(strict_types=1);

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HelpdeskConversation extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected static function newFactory(): \Database\Factories\HelpdeskConversationFactory
    {
        return \Database\Factories\HelpdeskConversationFactory::new();
    }

    protected $fillable = [
        'helpdesk_id',
        'message',
        'authorable_type',
        'authorable_id',
        'source',
        'is_internal',
        'bot_metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'bot_metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (HelpdeskConversation $conversation) {
            // Update last_activity_at on the parent ticket
            $conversation->ticket()->update([
                'last_activity_at' => now(),
            ]);
        });
    }

    // ========================================
    // Media Collections
    // ========================================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ticketConversationAttachment')
            ->useDisk('private');
    }

    // ========================================
    // Relationships
    // ========================================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Helpdesk::class, 'helpdesk_id');
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================
    // Accessors
    // ========================================

    public function getIsFromBotAttribute(): bool
    {
        return $this->source === 'bot';
    }

    public function getIsFromHumanAttribute(): bool
    {
        return $this->source === 'human';
    }

    public function getIsFromUserAttribute(): bool
    {
        return $this->authorable_type === User::class;
    }

    public function getIsFromAdminAttribute(): bool
    {
        return $this->authorable_type === Admin::class;
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->is_from_bot) {
            return config('helpdesk.chatbot.behavior.bot_name', 'AI Assistant');
        }

        return $this->authorable?->name ?? 'Unknown';
    }

    public function getAuthorAvatarAttribute(): ?string
    {
        if ($this->is_from_bot) {
            return null; // Use bot icon
        }

        return $this->authorable?->avatar_url ?? null;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeFromBot(Builder $query): Builder
    {
        return $query->where('source', 'bot');
    }

    public function scopeFromHuman(Builder $query): Builder
    {
        return $query->where('source', 'human');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('is_internal', true);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeOldestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Messaging;

use App\Casts\MessageTypeCast;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Message model for conversation messages.
 */
class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'sender_user_id',
        'sender_admin_id',
        'body',
        'type',
        'attachments',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => MessageTypeCast::class,
            'attachments' => 'array',
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Message $message) {
            if (! $message->uuid) {
                $message->uuid = (string) Str::uuid();
            }
        });

        static::created(function (Message $message) {
            // Update conversation's last message timestamp
            $message->conversation?->touchLastMessage();

            // Increment unread count for recipient
            if ($message->sender_user_id && $message->conversation) {
                $message->conversation->incrementUnreadFor($message->senderUser);
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function senderAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sender_admin_id');
    }

    // ========================================
    // Accessors
    // ========================================

    /**
     * Get the sender (user or admin).
     */
    public function getSenderAttribute(): User|Admin|null
    {
        return $this->senderUser ?? $this->senderAdmin;
    }

    /**
     * Get sender name.
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->senderUser) {
            return $this->senderUser->name;
        }

        if ($this->senderAdmin) {
            return $this->senderAdmin->name ?? 'Admin';
        }

        return 'System';
    }

    /**
     * Check if message is from admin.
     */
    public function getIsFromAdminAttribute(): bool
    {
        return $this->sender_admin_id !== null;
    }

    /**
     * Check if message is read.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if message has attachments.
     */
    public function getHasAttachmentsAttribute(): bool
    {
        return ! empty($this->attachments);
    }

    // ========================================
    // Query Scopes
    // ========================================

    /**
     * Get unread messages.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Get messages from a specific user.
     */
    public function scopeFromUser(Builder $query, User $user): Builder
    {
        return $query->where('sender_user_id', $user->id);
    }

    /**
     * Get messages from admin.
     */
    public function scopeFromAdmin(Builder $query): Builder
    {
        return $query->whereNotNull('sender_admin_id');
    }

    /**
     * Get messages of a specific type.
     */
    public function scopeOfType(Builder $query, MessageTypeCast $type): Builder
    {
        return $query->where('type', $type);
    }

    // ========================================
    // Actions
    // ========================================

    /**
     * Mark message as read.
     */
    public function markAsRead(): self
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }

        return $this;
    }

    /**
     * Check if this message was sent by a specific user.
     */
    public function wasSentBy(User $user): bool
    {
        return $this->sender_user_id === $user->id;
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}





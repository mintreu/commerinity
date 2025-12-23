<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Conversation model for user-to-user and admin broadcast messaging.
 */
class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_one_id',
        'user_two_id',
        'is_broadcast',
        'admin_id',
        'subject',
        'last_message_at',
        'unread_count_user_one',
        'unread_count_user_two',
    ];

    protected function casts(): array
    {
        return [
            'is_broadcast' => 'boolean',
            'last_message_at' => 'datetime',
            'unread_count_user_one' => 'integer',
            'unread_count_user_two' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Conversation $conversation) {
            if (! $conversation->uuid) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    // ========================================
    // Accessors
    // ========================================

    /**
     * Get the other participant in a conversation.
     */
    public function getOtherParticipant(User $user): ?User
    {
        if ($this->is_broadcast) {
            return null;
        }

        if ($this->user_one_id === $user->id) {
            return $this->userTwo;
        }

        return $this->userOne;
    }

    /**
     * Get unread count for a specific user.
     */
    public function getUnreadCountFor(User $user): int
    {
        if ($this->user_one_id === $user->id) {
            return $this->unread_count_user_one;
        }

        if ($this->user_two_id === $user->id) {
            return $this->unread_count_user_two;
        }

        return 0;
    }

    // ========================================
    // Query Scopes
    // ========================================

    /**
     * Get conversations for a specific user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id);
        });
    }

    /**
     * Get broadcast messages (from admin).
     */
    public function scopeBroadcast(Builder $query): Builder
    {
        return $query->where('is_broadcast', true);
    }

    /**
     * Get direct messages (user-to-user).
     */
    public function scopeDirect(Builder $query): Builder
    {
        return $query->where('is_broadcast', false);
    }

    /**
     * Get conversations with unread messages for a user.
     */
    public function scopeWithUnread(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where(function ($sub) use ($user) {
                $sub->where('user_one_id', $user->id)
                    ->where('unread_count_user_one', '>', 0);
            })->orWhere(function ($sub) use ($user) {
                $sub->where('user_two_id', $user->id)
                    ->where('unread_count_user_two', '>', 0);
            });
        });
    }

    // ========================================
    // Actions
    // ========================================

    /**
     * Find or create a conversation between two users.
     */
    public static function findOrCreateBetween(User $userOne, User $userTwo, ?string $subject = null): self
    {
        // Ensure consistent ordering
        $userOneId = min($userOne->id, $userTwo->id);
        $userTwoId = max($userOne->id, $userTwo->id);

        return static::firstOrCreate(
            [
                'user_one_id' => $userOneId,
                'user_two_id' => $userTwoId,
                'is_broadcast' => false,
            ],
            [
                'subject' => $subject,
            ]
        );
    }

    /**
     * Create a broadcast conversation from admin.
     */
    public static function createBroadcast(Admin $admin, string $subject): self
    {
        return static::create([
            'is_broadcast' => true,
            'admin_id' => $admin->id,
            'subject' => $subject,
        ]);
    }

    /**
     * Update last message timestamp.
     */
    public function touchLastMessage(): self
    {
        $this->update(['last_message_at' => now()]);

        return $this;
    }

    /**
     * Increment unread count for the other user.
     */
    public function incrementUnreadFor(User $sender): self
    {
        if ($this->user_one_id === $sender->id) {
            $this->increment('unread_count_user_two');
        } elseif ($this->user_two_id === $sender->id) {
            $this->increment('unread_count_user_one');
        }

        return $this;
    }

    /**
     * Mark conversation as read for a user.
     */
    public function markAsReadFor(User $user): self
    {
        if ($this->user_one_id === $user->id) {
            $this->update(['unread_count_user_one' => 0]);
        } elseif ($this->user_two_id === $user->id) {
            $this->update(['unread_count_user_two' => 0]);
        }

        // Mark all messages as read
        $this->messages()
            ->whereNull('read_at')
            ->where('sender_user_id', '!=', $user->id)
            ->update(['read_at' => now()]);

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

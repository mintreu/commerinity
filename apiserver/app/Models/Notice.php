<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Notice model for dashboard promotional messages and announcements.
 */
class Notice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'content',
        'type',
        'cta_text',
        'cta_link',
        'icon',
        'color',
        'image_url',
        'target_user_types',
        'target_stages',
        'is_global',
        'starts_at',
        'ends_at',
        'is_active',
        'priority',
        'created_by',
        'views_count',
        'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'target_user_types' => 'array',
            'target_stages' => 'array',
            'is_global' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
            'views_count' => 'integer',
            'clicks_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Notice $notice) {
            if (! $notice->uuid) {
                $notice->uuid = (string) Str::uuid();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function dismissedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notice_dismissals')
            ->withPivot('dismissed_at');
    }

    // ========================================
    // Query Scopes
    // ========================================

    /**
     * Get only active notices.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get notices within their schedule.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        });
    }

    /**
     * Get notices for a specific user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            // Global notices
            $q->where('is_global', true);

            // Or targeted by user type
            if ($user->type) {
                $q->orWhereJsonContains('target_user_types', $user->type->value);
            }

            // Or targeted by stage
            $currentSubscription = $user->subscriptions()->where('status', 'active')->latest()->first();
            if ($currentSubscription?->stage_id) {
                $q->orWhereJsonContains('target_stages', $currentSubscription->stage_id);
            }
        });
    }

    /**
     * Exclude dismissed notices for a user.
     */
    public function scopeNotDismissedBy(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('dismissedByUsers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    /**
     * Order by priority (higher first).
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority')->orderByDesc('created_at');
    }

    // ========================================
    // Accessors
    // ========================================

    /**
     * Check if notice is currently visible.
     */
    public function getIsVisibleAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get type color class.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'warning' => 'warning',
            'success' => 'success',
            'promo' => 'purple',
            default => 'info',
        };
    }

    /**
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match ($this->type) {
            'warning' => 'i-lucide-alert-triangle',
            'success' => 'i-lucide-check-circle',
            'promo' => 'i-lucide-gift',
            default => 'i-lucide-info',
        };
    }

    // ========================================
    // Actions
    // ========================================

    /**
     * Dismiss notice for a user.
     */
    public function dismissFor(User $user): void
    {
        $this->dismissedByUsers()->syncWithoutDetaching([
            $user->id => ['dismissed_at' => now()],
        ]);
    }

    /**
     * Record a view.
     */
    public function recordView(): void
    {
        $this->increment('views_count');
    }

    /**
     * Record a click.
     */
    public function recordClick(): void
    {
        $this->increment('clicks_count');
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

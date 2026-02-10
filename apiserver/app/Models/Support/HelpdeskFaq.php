<?php

declare(strict_types=1);

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HelpdeskFaq extends Model
{
    use HasFactory;

    protected static function newFactory(): \Database\Factories\HelpdeskFaqFactory
    {
        return \Database\Factories\HelpdeskFaqFactory::new();
    }

    protected $fillable = [
        'url',
        'question',
        'answer',
        'topic_id',
        'audience_type',
        'audience_id',
        'active',
        'order',
        'views',
        'helpful_count',
        'not_helpful_count',
        'tags',
        'keywords',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
            'views' => 'integer',
            'helpful_count' => 'integer',
            'not_helpful_count' => 'integer',
            'tags' => 'array',
            'keywords' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HelpdeskFaq $faq) {
            if (! $faq->url) {
                $faq->url = Str::slug($faq->question);
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function topic(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTopic::class, 'topic_id');
    }

    public function audience(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    // ========================================
    // Accessors
    // ========================================

    public function getHelpfulPercentageAttribute(): float
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return 0.0;
        }

        return round(($this->helpful_count / $total) * 100, 1);
    }

    public function getTotalFeedbackAttribute(): int
    {
        return $this->helpful_count + $this->not_helpful_count;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeByTopic(Builder $query, int $topicId): Builder
    {
        return $query->where('topic_id', $topicId);
    }

    public function scopeForAudience(Builder $query, ?string $audienceType = null, ?int $audienceId = null): Builder
    {
        return $query->where(function ($q) use ($audienceType, $audienceId) {
            // Public FAQs (no specific audience)
            $q->whereNull('audience_type');

            // If specific audience provided, include those too
            if ($audienceType && $audienceId) {
                $q->orWhere(function ($q2) use ($audienceType, $audienceId) {
                    $q2->where('audience_type', $audienceType)
                        ->where('audience_id', $audienceId);
                });
            }
        });
    }

    public function scopeTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeKeyword(Builder $query, string $keyword): Builder
    {
        return $query->whereJsonContains('keywords', $keyword);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('question', 'like', "%{$search}%")
                ->orWhere('answer', 'like', "%{$search}%");
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('question');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('views');
    }

    public function scopeMostHelpful(Builder $query): Builder
    {
        return $query->orderByDesc('helpful_count');
    }

    // ========================================
    // Actions
    // ========================================

    public function incrementViews(): self
    {
        if (config('helpdesk.faq.track_views', true)) {
            $this->increment('views');
        }

        return $this;
    }

    public function markHelpful(): self
    {
        if (config('helpdesk.faq.feedback_enabled', true)) {
            $this->increment('helpful_count');
        }

        return $this;
    }

    public function markNotHelpful(): self
    {
        if (config('helpdesk.faq.feedback_enabled', true)) {
            $this->increment('not_helpful_count');
        }

        return $this;
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'url';
    }
}





<?php

declare(strict_types=1);

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HelpdeskTopic extends Model
{
    use HasFactory;

    protected static function newFactory(): \Database\Factories\HelpdeskTopicFactory
    {
        return \Database\Factories\HelpdeskTopicFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'tickable',
        'active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'tickable' => 'boolean',
            'active' => 'boolean',
            'order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HelpdeskTopic $topic) {
            if (! $topic->slug) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function tickets(): HasMany
    {
        return $this->hasMany(Helpdesk::class, 'topic_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(HelpdeskFaq::class, 'topic_id');
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeTicketable(Builder $query): Builder
    {
        return $query->where('tickable', true);
    }

    public function scopeNonTicketable(Builder $query): Builder
    {
        return $query->where('tickable', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // ========================================
    // Accessors
    // ========================================

    public function getTicketsCountAttribute(): int
    {
        return $this->tickets()->count();
    }

    public function getFaqsCountAttribute(): int
    {
        return $this->faqs()->active()->count();
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

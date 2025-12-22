<?php

namespace Mintreu\LaravelHelpdesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskFaq extends Model
{
    /** @use HasFactory<\Database\Factories\HelpdeskFaqFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'url',
        'question',
        'answer',
        'topic_id',
        'active',
        'order',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
        'tags' => 'array', // store tags as JSON array
    ];

    /**
     * Relationship: FAQ belongs to a topic.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTopic::class, 'topic_id','id');
    }

    /**
     * Scope: Only active FAQs.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: Filter FAQs by topic slug.
     */
    public function scopeByTopic($query, string $slug)
    {
        return $query->whereHas('topic', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    /**
     * Helper: Check if FAQ has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Scope: Search FAQs by tag
     */
    public function scopeTag($query, string $tag)
    {
        return $query->where('tags', 'like', "%{$tag}%");
    }

}

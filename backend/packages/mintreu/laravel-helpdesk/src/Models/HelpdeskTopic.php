<?php

namespace Mintreu\LaravelHelpdesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class HelpdeskTopic extends Model
{
    /** @use HasFactory<\Database\Factories\HelpdeskTopicFactory> */
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'name',
        'slug',
        'tickable',
        'description',
        'order',
        'active',
    ];

    // Casts
    protected $casts = [
        'tickable' => 'boolean',
        'active'   => 'boolean',
        'order'    => 'integer',
    ];

    // Mutators / Accessors
    /**
     * Automatically generate slug from name if not set
     */
    protected static function booted()
    {
        static::creating(function ($topic) {
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }




    // Relationships
    /**
     * Tickets under this topic
     */
    public function tickets()
    {
        return $this->hasMany(Helpdesk::class, 'topic_id');
    }

    /**
     * Optionally, conversations under this topic through tickets
     */
    public function conversations(): HasManyThrough
    {
        return $this->hasManyThrough(
            HelpdeskConversation::class,
            Helpdesk::class,
            'topic_id',           // Foreign key on Helpdesk table
            'helpdesk_id',        // Foreign key on HelpdeskConversation table
            'id',                 // Local key on this table
            'id'                  // Local key on Helpdesk table
        );
    }


    // Scopes
    /**
     * Only active topics
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Only tickable topics
     */
    public function scopeTickable($query)
    {
        return $query->where('tickable', true);
    }
}

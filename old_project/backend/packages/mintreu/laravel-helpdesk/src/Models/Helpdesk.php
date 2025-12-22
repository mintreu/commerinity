<?php

namespace Mintreu\LaravelHelpdesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskPriorityCast;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskStatusCast;
use Mintreu\Toolkit\Traits\HasUnique;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Helpdesk extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\HelpdeskFactory> */
    use HasFactory,HasUnique,InteractsWithMedia;



    // Mass assignable
    protected $fillable = [
        'uuid',
        'title',
        'description',
        'priority',
        'status',
        'topic_id',
        'authorable_type',
        'authorable_id',
    ];

    // Casts
    protected $casts = [
        'uuid' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'priority'  => HelpdeskPriorityCast::class,
        'status'    => HelpdeskStatusCast::class
    ];



    protected static function booted()
    {
        static::creating(function ($user){
            $user->setUniqueCode('uuid',8,'TICKET-');
        });
        parent::booted();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ticketAttachment');

    }


    /**
     * Polymorphic relation: who created the ticket
     */
    public function authorable(): MorphTo
    {
        return $this->morphTo('authorable');
    }

    /**
     * Ticket conversations
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(HelpdeskConversation::class,'helpdesk_id','id');
    }

    /**
     * Helper: latest conversation
     */
    public function latestConversation(): ?HelpdeskConversation
    {
        return $this->conversations()->latest()->first();
    }

    /**
     * Helper: is ticket open?
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Helper: change status
     */
    public function markAs(string $status): static
    {
        $this->status = $status;
        $this->save();
        return $this;
    }


    public function topic()
    {
        return $this->belongsTo(HelpdeskTopic::class,'topic_id','id');
    }






}

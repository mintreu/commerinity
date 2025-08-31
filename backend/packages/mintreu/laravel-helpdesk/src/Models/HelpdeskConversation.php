<?php

namespace Mintreu\LaravelHelpdesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HelpdeskConversation extends Model
{
    /** @use HasFactory<\Database\Factories\HelpdeskConversationFactory> */
    use HasFactory;


    protected $fillable = [
        'helpdesk_id',
        'message',
        'authorable_type',
        'authorable_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Polymorphic relation: who wrote the conversation
     */
    public function author(): MorphTo
    {
        return $this->morphTo('authorable');
    }

    /**
     * Belongs to ticket
     */
    public function helpdesk(): BelongsTo
    {
        return $this->belongsTo(Helpdesk::class,'helpdesk_id','id');
    }

    /**
     * Helper: snippet for preview
     */
    public function snippet(int $length = 50): string
    {
        return strlen($this->message) > $length
            ? substr($this->message, 0, $length) . '...'
            : $this->message;
    }


}

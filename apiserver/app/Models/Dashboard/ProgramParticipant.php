<?php

namespace App\Models\Dashboard;

use App\Models\Traits\HasUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramParticipant extends Model
{
    /** @use HasFactory<\Database\Factories\Dashboard\ProgramParticipantFactory> */
    use HasFactory;

    use HasUnique;

    protected $fillable = [
        'uuid',
        'program_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProgramParticipant $participant) {
            $participant->setUniqueUuid('uuid', 20);
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'invited_by');
    }
}

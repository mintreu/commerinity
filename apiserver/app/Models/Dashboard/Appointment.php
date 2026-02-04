<?php

namespace App\Models\Dashboard;

use App\Models\Traits\HasUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\Dashboard\AppointmentFactory> */
    use HasFactory;

    use HasUnique;

    protected $fillable = [
        'uuid',
        'creator_type',
        'creator_id',
        'advisor_id',
        'mentor_id',
        'attendee_user_id',
        'title',
        'agenda',
        'meeting_mode',
        'meeting_link',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            $appointment->setUniqueUuid('uuid', 20);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'advisor_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'mentor_id');
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'attendee_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AppointmentParticipant::class);
    }
}

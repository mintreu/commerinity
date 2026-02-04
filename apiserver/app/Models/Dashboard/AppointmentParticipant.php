<?php

namespace App\Models\Dashboard;

use App\Models\Traits\HasUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentParticipant extends Model
{
    use HasFactory;
    use HasUnique;

    protected $fillable = [
        'uuid',
        'appointment_id',
        'user_id',
        'role',
    ];

    protected static function booted(): void
    {
        static::creating(fn (AppointmentParticipant $participant) => $participant->setUniqueUuid('uuid', 20));
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

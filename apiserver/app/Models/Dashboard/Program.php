<?php

namespace App\Models\Dashboard;

use App\Models\Traits\HasAddress;
use App\Models\Traits\HasUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Dashboard\ProgramParticipant;

class Program extends Model
{
    /** @use HasFactory<\Database\Factories\Dashboard\ProgramFactory> */
    use HasFactory;

    use HasUnique;
    use HasAddress;

    protected $fillable = [
        'uuid',
        'creator_type',
        'creator_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Program $program) {
            $program->setUniqueUuid('uuid', 20);
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

    public function participants(): HasMany
    {
        return $this->hasMany(ProgramParticipant::class);
    }
}

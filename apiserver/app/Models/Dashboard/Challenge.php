<?php

namespace App\Models\Dashboard;

use App\Models\Traits\HasUnique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\Dashboard\ChallengeFactory> */
    use HasFactory;

    use HasUnique;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'status',
        'start_at',
        'end_at',
        'reward_type',
        'reward_value',
        'goal_type',
        'goal_value',
        'targetable_type',
        'targetable_id',
        'target_user_type',
        'target_level_id',
        'target_stage_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'reward_value' => 'integer',
            'goal_value' => 'integer',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Challenge $challenge) {
            $challenge->setUniqueUuid('uuid', 20);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function targetLevel(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Level::class, 'target_level_id');
    }

    public function targetStage(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stage::class, 'target_stage_id');
    }
}

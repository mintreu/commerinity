<?php

namespace App\Models\Lifecycle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelTaskProgress extends Model
{
    /** @use HasFactory<\Database\Factories\Lifecycle\UserLevelTaskProgressFactory> */
    use HasFactory;

    protected $table = 'user_level_task_progress';

    protected $fillable = [
        'score',
        'level_task_id',
        'player_id',
        'player_type',
        'is_complete',
    ];

    protected $casts = [
        'score' => 'integer',
        'is_complete' => 'boolean',
    ];

    /**
     * Each progress entry belongs to a specific task.
     */
    public function levelTask()
    {
        return $this->belongsTo(LevelTask::class);
    }

    /**
     * Polymorphic relationship: player could be a User, Team, etc.
     */
    public function player()
    {
        return $this->morphTo();
    }

}

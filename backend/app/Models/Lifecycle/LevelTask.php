<?php

namespace App\Models\Lifecycle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelTask extends Model
{
    /** @use HasFactory<\Database\Factories\Lifecycle\LevelTaskFactory> */
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'url',
        'description',
        'score',
        'min_eligible_score',
        'min_progress',
        'game_type',
        'level_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'min_progress' => 'array',
        'score' => 'integer',
        'min_eligible_score' => 'integer',
    ];



    public function level()
    {
        return $this->belongsTo(Level::class,'level_id');
    }


    /**
     * A level task has many progress entries (from multiple players).
     */
    public function progressions()
    {
        return $this->hasMany(UserLevelTaskProgress::class, 'level_task_id');
    }




}

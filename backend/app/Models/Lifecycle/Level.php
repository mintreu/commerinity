<?php

namespace App\Models\Lifecycle;

use App\Models\Traits\HasSales;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mintreu\Toolkit\Traits\HasRecordNavigator;

class Level extends Model
{
    /** @use HasFactory<\Database\Factories\Lifecycle\LevelFactory> */
    use HasFactory,HasRecordNavigator,HasSales;



    protected $fillable = [
        'name',
        'url',
        'team_member_limit',
        'stage_id',
        'validate_years', // use for expire and re new level
        'joining_bonus', // percentage
        'status'
    ];

    protected function casts()
    {
        return [
            'validate_years' => 'integer',
            'team_member_limit' => 'integer',
            'status' => 'boolean'
        ];
    }


    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class,'stage_id','id');
    }


    public function users(): HasMany
    {
        return $this->hasMany(User::class,'level_id','id');
    }


}

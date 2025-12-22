<?php

namespace Mintreu\LaravelGeokit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    /** @use HasFactory<\Database\Factories\Localization\BlockFactory> */
    use HasFactory;


    protected $fillable = [
        'name',
        'url',
        'state_code',
        'district_name',
        'latitude',
        'longitude',
    ];


    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class,  'state_code','code');
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Address::class,'block_id','id');
    }


}

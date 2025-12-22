<?php

namespace Mintreu\LaravelGeokit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','code'
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }



    public function address(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Address::class,'state_code','code');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id','id');
    }


    public function blocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Block::class,'state_code','code');
    }

}

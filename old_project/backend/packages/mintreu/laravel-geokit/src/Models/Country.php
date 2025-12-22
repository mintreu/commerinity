<?php

namespace Mintreu\LaravelGeokit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'isd_code',
        'address_format',
        'postcode_required',
        'locale',
        'region',
        'timezone',
        'timezone_diff',
        'currency',
        'flag',
        'exchange_rate',
        'multiplier',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'isd_code' => 'integer',
        'postcode_required' => 'boolean',
        'exchange_rate' => 'array', // JSON cast to array
        'multiplier' => 'float',
        'is_active' => 'boolean',
    ];


    public function getRouteKeyName(): string
    {
        return 'iso_code_2';
    }



    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class,'country_code','iso_code_2');
    }


    public function states(): HasMany
    {
        return $this->hasMany(State::class,'country_id','id');
    }


}

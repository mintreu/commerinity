<?php

namespace Mintreu\LaravelGeokit\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;

trait HasAddress
{


    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }


    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }




}

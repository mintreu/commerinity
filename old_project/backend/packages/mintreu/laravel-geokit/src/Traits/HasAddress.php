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



    public function homeAddress(): MorphOne
    {
        return $this->address()->where('type',AddressTypeCast::HOME);
    }


    public function deliveryAddresses(): MorphMany
    {
        return $this->addresses()->where('type',AddressTypeCast::DELIVERY);
    }

    public function pickupAddresses(): MorphMany
    {
        return $this->addresses()->where('type',AddressTypeCast::DELIVERY);
    }


}

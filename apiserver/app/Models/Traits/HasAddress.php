<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Casts\AddressTypeCast;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * HasAddress Trait
 *
 * Add to any model that needs to have multiple addresses (User, Order, Warehouse, etc.)
 * Provides polymorphic address relationships with type filtering.
 *
 * Usage:
 *   use HasAddress;
 *   // $user->addresses - all addresses
 *   // $user->deliveryAddresses - only delivery addresses
 *   // $user->homeAddress - only home address
 */
trait HasAddress
{
    /**
     * Get all addresses for this model (polymorphic)
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(\App\Models\Address::class, 'addressable');
    }

    /**
     * Get single address (first one)
     */
    public function address(): MorphOne
    {
        return $this->morphOne(\App\Models\Address::class, 'addressable');
    }

    /**
     * Get home address only
     */
    public function homeAddress(): MorphOne
    {
        return $this->address()->where('type', AddressTypeCast::HOME->value);
    }

    /**
     * Get work address only
     */
    public function workAddress(): MorphOne
    {
        return $this->address()->where('type', AddressTypeCast::WORK->value);
    }

    /**
     * Get other address only
     */
    public function otherAddress(): MorphOne
    {
        return $this->address()->where('type', AddressTypeCast::OTHER->value);
    }

    /**
     * Get delivery addresses only
     */
    public function deliveryAddresses(): MorphMany
    {
        return $this->addresses()->where('type', AddressTypeCast::DELIVERY->value);
    }

    /**
     * Get pickup addresses only
     */
    public function pickupAddresses(): MorphMany
    {
        return $this->addresses()->where('type', AddressTypeCast::PICKUP->value);
    }

    /**
     * Get hub addresses only
     */
    public function hubAddresses(): MorphMany
    {
        return $this->addresses()->where('type', AddressTypeCast::HUB->value);
    }

    /**
     * Get service point addresses only
     */
    public function servicePointAddresses(): MorphMany
    {
        return $this->addresses()->where('type', AddressTypeCast::SERVICE_POINT->value);
    }

    /**
     * Get default address
     */
    public function defaultAddress(): ?\App\Models\Address
    {
        return $this->addresses()->where('default', true)->first();
    }

    /**
     * Set an address as default (unsets other defaults)
     */
    public function setDefaultAddress(\App\Models\Address $address): void
    {
        $this->addresses()->where('default', true)->update(['default' => false]);
        $address->update(['default' => true]);
    }

    /**
     * Add a new address
     */
    public function addAddress(array $attributes): \App\Models\Address
    {
        return $this->addresses()->create($attributes);
    }

    /**
     * Check if model has any addresses
     */
    public function hasAddresses(): bool
    {
        return $this->addresses()->exists();
    }

    /**
     * Get count of addresses
     */
    public function addressCount(): int
    {
        return $this->addresses()->count();
    }
}

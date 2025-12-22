<?php

namespace Mintreu\LaravelGeokit\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Mintreu\LaravelGeokit\Models\Address;

trait HasOrderAddresses
{
    use HasAddress;

    public function billingAddress(): ?BelongsTo
    {
        $key = property_exists($this, 'billingAddressKey')
            ? $this->billingAddressKey
            : 'billing_address_id';

        // 🔍 Ensure column exists
        if (!Schema::hasColumn($this->getTable(), $key)) {
            return null;
        }

        return $this->belongsTo(Address::class, $key);
    }

    public function shippingAddress(): ?BelongsTo
    {
        $key = property_exists($this, 'shippingAddressKey')
            ? $this->shippingAddressKey
            : 'shipping_address_id';

        // 🔍 Ensure column exists
        if (!Schema::hasColumn($this->getTable(), $key)) {
            return null;
        }

        return $this->belongsTo(Address::class, $key);
    }

    public function isShippingSameAsBilling(): bool
    {
        $billingKey = property_exists($this, 'billingAddressKey')
            ? $this->billingAddressKey
            : 'billing_address_id';

        $shippingKey = property_exists($this, 'shippingAddressKey')
            ? $this->shippingAddressKey
            : 'shipping_address_id';

        // If either column doesn't exist, always false
        if (!Schema::hasColumn($this->getTable(), $billingKey) ||
            !Schema::hasColumn($this->getTable(), $shippingKey)) {
            return false;
        }

        return $this->{$billingKey} === $this->{$shippingKey};
    }
}

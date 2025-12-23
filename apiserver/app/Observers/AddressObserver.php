<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Address;

final class AddressObserver
{
    /**
     * Handle the Address "creating" event.
     * Set first address as default automatically.
     */
    public function creating(Address $address): void
    {
        if ($address->addressable_id && $address->addressable_type) {
            $hasExisting = $address->addressable->addresses()->exists();

            if (! $hasExisting) {
                $address->default = true;
            }
        }
    }

    /**
     * Handle the Address "saving" event.
     * Ensure only one default address per user or per standalone type.
     */
    public function saving(Address $address): void
    {
        if (! $address->default) {
            return;
        }

        // For user addresses, only update other addresses of the same user
        if ($address->addressable_id && $address->addressable_type) {
            $address->addressable->addresses()
                ->where('id', '!=', $address->id ?? 0)
                ->update(['default' => false]);
        }

        // For standalone addresses (warehouses, stores), only update same type
        if (! $address->addressable_id && ! $address->addressable_type) {
            Address::standalone()
                ->where('type', $address->type)
                ->where('id', '!=', $address->id ?? 0)
                ->update(['default' => false]);
        }
    }
}

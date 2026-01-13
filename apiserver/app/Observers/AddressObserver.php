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
        // Skip auto-setting default for standalone addresses (warehouses, stores)
        if (! $address->addressable_id || ! $address->addressable_type) {
            return;
        }

        // Check if this is the first address for the addressable
        $hasExisting = Address::where('addressable_type', $address->addressable_type)
            ->where('addressable_id', $address->addressable_id)
            ->exists();

        if (! $hasExisting) {
            $address->default = true;
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
            Address::where('addressable_type', $address->addressable_type)
                ->where('addressable_id', $address->addressable_id)
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

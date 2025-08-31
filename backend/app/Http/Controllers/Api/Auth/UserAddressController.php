<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Geo\AddressIndexResource;
use App\Http\Resources\Geo\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelGeokit\Models\Block;

class UserAddressController extends Controller
{
    /**
     * Get all user addresses
     */
    public function getUserAllAddress(Request $request)
    {
        $user = $request->user();
        $user->load([
            'addresses',
            'addresses.block',
            'addresses.state',
            'addresses.country',
        ]);

        return AddressIndexResource::collection($user->addresses);
    }

    /**
     * Add a new user address
     */
    public function addUserAddress(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'type'          => 'required|string|in:home,work,other',
            'address_1'     => 'required|string',
            'landmark'      => 'required|string|max:255',
            'person_name'   => 'required|string|max:255',
            'person_mobile' => 'required|string|max:15',
            'state_code'    => 'required|string|size:2',
            'city'          => 'nullable|string|max:255',
            'village'       => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:10',
            'block_id'      => 'nullable|string',
            'person_email'  => 'nullable|email',
            'country_code'  => 'nullable|string|size:2',
        ]);
        $block = Block::firstWhere('url',$request->block_id);
        $validated['block_id'] = $block->id;
        $user = $request->user();

        $address = $user->addresses()->create(array_merge($validated, [
            'uuid'         => Str::orderedUuid()->toString(),
            'country_code' => $validated['country_code'] ?? 'IN',
        ]));

        return new AddressResource($address->fresh(['block', 'state', 'country']));
    }

    /**
     * Update an existing user address
     */
    public function updateUserAddress(Address $address, Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'type'          => 'required|string|in:home,work,other',
            'address_1'     => 'required|string',
            'landmark'      => 'required|string|max:255',
            'person_name'   => 'required|string|max:255',
            'person_mobile' => 'required|string|max:15',
            'state_code'    => 'required|string|size:2',
            'city'          => 'nullable|string|max:255',
            'village'       => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:10',
            'block_id'      => 'nullable|string|',
            'person_email'  => 'nullable|email',
            'country_code'  => 'nullable|string|size:2',
        ]);

        $block = Block::firstWhere('url',$request->block_id);
        $validated['block_id'] = $block->id;

        $address->update($validated);

        return new AddressResource($address->fresh(['block', 'state', 'country']));
    }
}

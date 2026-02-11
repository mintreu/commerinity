<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AddressController extends Controller
{
    /**
     * Display a listing of the authenticated user's addresses.
     */
    public function index(): AnonymousResourceCollection
    {
        $addresses = auth()->user()
            ->addresses()
            ->with(['country', 'state', 'district', 'block'])
            ->orderByDesc('default')
            ->orderBy('created_at')
            ->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Store a newly created address.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();

        $address = $user->addresses()->create($request->validated());

        $address->load(['country', 'state', 'district', 'block']);

        return response()->json([
            'message' => 'Address created successfully',
            'data' => new AddressResource($address),
        ], 201);
    }

    /**
     * Display the specified address.
     */
    public function show(Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_id !== auth()->id()) {
            return response()->json([
                'message' => 'Address not found',
            ], 404);
        }

        $address->load(['country', 'state', 'district', 'block']);

        return response()->json([
            'data' => new AddressResource($address),
        ]);
    }

    /**
     * Update the specified address.
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_id !== auth()->id()) {
            return response()->json([
                'message' => 'Address not found',
            ], 404);
        }

        $address->update($request->validated());

        $address->load(['country', 'state', 'district', 'block']);

        return response()->json([
            'message' => 'Address updated successfully',
            'data' => new AddressResource($address),
        ]);
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_id !== auth()->id()) {
            return response()->json([
                'message' => 'Address not found',
            ], 404);
        }

        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully',
        ], 200);
    }

    /**
     * Set the specified address as default.
     */
    public function setDefault(Address $address): JsonResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->addressable_id !== auth()->id()) {
            return response()->json([
                'message' => 'Address not found',
            ], 404);
        }

        // Setting default to true will automatically trigger
        // the model event to unset other addresses
        $address->update(['default' => true]);

        $address->load(['country', 'state', 'district', 'block']);

        return response()->json([
            'message' => 'Default address updated successfully',
            'data' => new AddressResource($address),
        ]);
    }
}

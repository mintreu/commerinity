<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GeoController extends Controller
{
    /**
     * Get all active countries
     */
    public function countries(): JsonResponse
    {
        $countries = Country::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'iso_code_2', 'iso_code_3', 'isd_code'])
            ->map(fn ($country) => [
                'value' => $country->iso_code_2,
                'label' => $country->name,
                'isd_code' => $country->isd_code,
            ]);

        return response()->json([
            'data' => $countries,
        ]);
    }

    /**
     * Get states for a country
     */
    public function states(Request $request): JsonResponse
    {
        $request->validate([
            'country_code' => 'required|string|size:2',
        ]);

        $countryCode = $request->input('country_code');

        $states = State::query()
            ->byCountryCode($countryCode)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn ($state) => [
                'value' => $state->code,
                'label' => $state->name,
            ]);

        return response()->json([
            'data' => $states,
        ]);
    }

    /**
     * Get blocks for a state
     */
    public function blocks(Request $request): JsonResponse
    {
        $request->validate([
            'state_code' => 'required|string',
        ]);

        $stateCode = $request->input('state_code');

        $blocks = Block::query()
            ->byState($stateCode)
            ->orderBy('name')
            ->get(['id', 'name', 'district_name', 'latitude', 'longitude'])
            ->map(fn ($block) => [
                'value' => $block->id,
                'label' => $block->name,
                'district' => $block->district_name,
                'coordinates' => $block->hasCoordinates() ? [
                    'lat' => $block->latitude,
                    'lng' => $block->longitude,
                ] : null,
            ]);

        return response()->json([
            'data' => $blocks,
        ]);
    }

    /**
     * Get districts for a state (unique list)
     */
    public function districts(Request $request): JsonResponse
    {
        $request->validate([
            'state_code' => 'required|string',
        ]);

        $stateCode = $request->input('state_code');

        $districts = Block::query()
            ->byState($stateCode)
            ->distinct()
            ->orderBy('district_name')
            ->pluck('district_name')
            ->filter()
            ->map(fn ($district) => [
                'value' => $district,
                'label' => $district,
            ])
            ->values();

        return response()->json([
            'data' => $districts,
        ]);
    }
}

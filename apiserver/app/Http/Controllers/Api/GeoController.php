<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\District;
use App\Models\Geo\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class GeoController extends Controller
{
    /**
     * Get all active countries
     */
    public function countries(): JsonResponse
    {
        $withStates = request()->boolean('with_states');
        $cacheKey = $withStates ? 'geo:countries:active:with_states' : 'geo:countries:active';

        try {
            $countries = Cache::remember($cacheKey, now()->addHours(12), function () use ($withStates) {
                $query = Country::query()
                ->active()
                ->orderBy('name')
                ->select(['id', 'name', 'iso_code_2', 'iso_code_3', 'isd_code']);

                if ($withStates) {
                    $query->with(['states' => function ($stateQuery) {
                        $stateQuery->orderBy('name')->select(['id', 'name', 'code', 'country_id']);
                    }]);
                }

                return $query->get()->map(function ($country) use ($withStates) {
                    $payload = [
                        'value' => $country->iso_code_2,
                        'label' => $country->name,
                        'isd_code' => $country->isd_code,
                    ];

                    if ($withStates) {
                        $payload['states'] = $country->states->map(fn ($state) => [
                            'value' => $state->code,
                            'label' => $state->name,
                        ])->values()->all();
                    }

                    return $payload;
                })->values()->all();
            });
        } catch (\Throwable $e) {
            Log::error('Geo countries failed', [
                'with_states' => $withStates,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'message' => 'Failed to load countries',
            ], 500);
        }

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
        try {
            $states = Cache::remember("geo:states:{$countryCode}", now()->addHours(12), function () use ($countryCode) {
                return State::query()
                    ->byCountryCode($countryCode)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code'])
                    ->map(fn ($state) => [
                        'value' => $state->code,
                        'label' => $state->name,
                    ])
                    ->values()
                    ->all();
            });
        } catch (\Throwable $e) {
            Log::error('Geo states failed', [
                'country_code' => $countryCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'message' => 'Failed to load states',
            ], 500);
        }

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
            'district_id' => 'nullable|integer|exists:districts,id',
        ]);

        $stateCode = $request->input('state_code');
        $districtId = $request->integer('district_id') ?: null;
        try {
            $cacheKey = $districtId
                ? "geo:blocks:{$stateCode}:district:{$districtId}"
                : "geo:blocks:{$stateCode}";

            $blocks = Cache::remember($cacheKey, now()->addHours(6), function () use ($stateCode, $districtId) {
                return Block::query()
                    ->byState($stateCode)
                    ->when($districtId, fn ($query) => $query->where('district_id', $districtId))
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
                    ])
                    ->values()
                    ->all();
            });
        } catch (\Throwable $e) {
            Log::error('Geo blocks failed', [
                'state_code' => $stateCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'message' => 'Failed to load blocks',
            ], 500);
        }

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
        try {
            $districts = Cache::remember("geo:districts:{$stateCode}", now()->addHours(6), function () use ($stateCode) {
                return District::query()
                    ->whereHas('state', fn ($query) => $query->where('code', $stateCode))
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn ($district) => [
                        'value' => $district->id,
                        'label' => $district->name,
                    ])
                    ->values()
                    ->all();
            });
        } catch (\Throwable $e) {
            Log::error('Geo districts failed', [
                'state_code' => $stateCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'data' => [],
                'message' => 'Failed to load districts',
            ], 500);
        }

        return response()->json([
            'data' => $districts,
        ]);
    }
}

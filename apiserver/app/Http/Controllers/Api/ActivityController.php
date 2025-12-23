<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * ActivityController
 *
 * Handles tracking user activities from client side.
 * Activities are stored for admin analytics but not exposed to users.
 */
final class ActivityController extends Controller
{
    /**
     * Track a client-side activity.
     * This endpoint accepts various activity types from the frontend.
     */
    public function track(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'properties' => ['nullable', 'array'],
            'properties.page_path' => ['nullable', 'string', 'max:255'],
            'properties.page_title' => ['nullable', 'string', 'max:255'],
            'properties.referrer' => ['nullable', 'string', 'max:500'],
            'properties.action' => ['nullable', 'string', 'max:100'],
            'properties.target' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'array'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
            'location.city' => ['nullable', 'string', 'max:100'],
            'location.country' => ['nullable', 'string', 'max:100'],
            'screen' => ['nullable', 'array'],
            'screen.width' => ['nullable', 'integer'],
            'screen.height' => ['nullable', 'integer'],
            'screen.viewport_width' => ['nullable', 'integer'],
            'screen.viewport_height' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $activityService = UserActivityService::make($request);

        $event = $request->input('event');
        $description = $request->input('description', "Event: {$event}");
        $properties = $request->input('properties', []);

        // Log the activity
        $activityService->logActivity(
            user: $user,
            event: $event,
            description: $description,
            properties: $properties,
        );

        // Return minimal response (don't expose activity data to client)
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Track a page view event.
     */
    public function trackPageView(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page_path' => ['required', 'string', 'max:255'],
            'page_title' => ['required', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'array'],
            'screen' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $activityService = UserActivityService::make($request);

        $activityService->logPageView(
            user: $user,
            pagePath: $request->input('page_path'),
            pageTitle: $request->input('page_title'),
            referrer: $request->input('referrer'),
        );

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Track a user action (click, submit, etc.).
     */
    public function trackAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => ['required', 'string', 'max:100'],
            'target' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'location' => ['nullable', 'array'],
            'screen' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $activityService = UserActivityService::make($request);

        $activityService->logAction(
            user: $user,
            action: $request->input('action'),
            target: $request->input('target'),
            data: $request->input('data'),
        );

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Batch track multiple activities at once.
     * Useful for offline-first apps that queue activities.
     */
    public function trackBatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'activities' => ['required', 'array', 'max:50'],
            'activities.*.event' => ['required', 'string', 'max:100'],
            'activities.*.description' => ['nullable', 'string', 'max:500'],
            'activities.*.properties' => ['nullable', 'array'],
            'activities.*.timestamp' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $activityService = UserActivityService::make($request);
        $activities = $request->input('activities', []);
        $tracked = 0;

        foreach ($activities as $activity) {
            $activityService->logActivity(
                user: $user,
                event: $activity['event'],
                description: $activity['description'] ?? "Event: {$activity['event']}",
                properties: array_merge(
                    $activity['properties'] ?? [],
                    ['client_timestamp' => $activity['timestamp'] ?? null]
                ),
            );
            $tracked++;
        }

        return response()->json([
            'success' => true,
            'tracked' => $tracked,
        ]);
    }
}

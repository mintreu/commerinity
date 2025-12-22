<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushNotificationController extends Controller
{
    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|url',
            'subscription.keys' => 'required|array',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subscription = $request->input('subscription');

            // Get user (authenticated or by email)
            $user = Auth::check()
                ? Auth::user()
                : User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found. Please login or provide registered email.'
                ], 404);
            }

            // Save push subscription
            $user->updatePushSubscription(
                $subscription['endpoint'],
                $subscription['keys']['p256dh'],
                $subscription['keys']['auth'],
                $subscription['contentEncoding'] ?? null
            );

            return response()->json([
                'status' => true,
                'message' => 'Push notifications enabled successfully!',
                'data' => [
                    'endpoint' => $subscription['endpoint'],
                    'user' => $user->only(['id', 'name', 'email'])
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user->deletePushSubscription($request->endpoint);

            return response()->json([
                'status' => true,
                'message' => 'Successfully unsubscribed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to unsubscribe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to a user
     */
    public function sendToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'icon' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($request->user_id);

            $user->notify(new PushNotification(
                $request->title,
                $request->body,
                $request->icon ?? '/icon-192x192.png',
                $request->url
            ));

            return response()->json([
                'status' => true,
                'message' => 'Notification sent successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to all users with push subscriptions
     */
    public function sendToAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'icon' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $users = User::whereHas('pushSubscriptions')->get();

            foreach ($users as $user) {
                $user->notify(new PushNotification(
                    $request->title,
                    $request->body,
                    $request->icon ?? '/icon-192x192.png',
                    $request->url
                ));
            }

            return response()->json([
                'status' => true,
                'message' => "Notification sent to {$users->count()} users!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to users by level
     */
    public function sendToLevel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level_id' => 'required|exists:levels,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'icon' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $users = User::where('level_id', $request->level_id)
                ->whereHas('pushSubscriptions')
                ->get();

            foreach ($users as $user) {
                $user->notify(new PushNotification(
                    $request->title,
                    $request->body,
                    $request->icon ?? '/icon-192x192.png',
                    $request->url
                ));
            }

            return response()->json([
                'status' => true,
                'message' => "Notification sent to {$users->count()} users!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get VAPID public key
     */
    public function getVapidPublicKey()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'public_key' => config('webpush.vapid.public_key')
            ]
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

final class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     * Transforms Filament notification format to frontend-friendly format.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        // Transform notifications to frontend format
        $notifications->getCollection()->transform(fn ($notification) => $this->transformNotification($notification));

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Transform a notification to frontend-friendly format.
     * Handles both Filament format and legacy format.
     */
    private function transformNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        // Check if it's Filament format (has 'format' key or specific structure)
        if (isset($data['format']) && $data['format'] === 'filament') {
            // Filament format: extract title, body, icon, actions
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => [
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['body'] ?? '',
                    'body' => $data['body'] ?? '',
                    'icon' => $data['icon'] ?? 'heroicon-o-bell',
                    'color' => $data['color'] ?? null,
                    'status' => $data['status'] ?? null,
                    'action_url' => $this->extractActionUrl($data),
                    'action_text' => $this->extractActionText($data),
                    'actions' => $data['actions'] ?? [],
                ],
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at->toIso8601String(),
            ];
        }

        // Legacy format or custom format
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'data' => [
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? $data['body'] ?? '',
                'body' => $data['body'] ?? $data['message'] ?? '',
                'icon' => $data['icon'] ?? 'heroicon-o-bell',
                'action_url' => $data['action_url'] ?? null,
                'action_text' => $data['action_text'] ?? null,
                'type' => $data['type'] ?? 'general',
            ],
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }

    /**
     * Extract action URL from Filament notification actions.
     */
    private function extractActionUrl(array $data): ?string
    {
        if (isset($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if (isset($action['url'])) {
                    return $action['url'];
                }
            }
        }

        return null;
    }

    /**
     * Extract action text from Filament notification actions.
     */
    private function extractActionText(array $data): ?string
    {
        if (isset($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if (isset($action['label'])) {
                    return $action['label'];
                }
            }
        }

        return null;
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}

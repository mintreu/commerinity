<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationController extends Controller
{
    /**
     * GET /account/notifications
     * query: page, per_page, include_read, filter
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $perPage     = (int) $request->query('per_page', 15);
        $includeRead = filter_var($request->query('include_read', true), FILTER_VALIDATE_BOOLEAN);
        $filter      = $request->query('filter', 'all'); // all, unread, today, week

        $query = $user->notifications()->latest();

        // Apply filters
        if (!$includeRead || $filter === 'unread') {
            $query->whereNull('read_at');
        }

        if ($filter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($filter === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        }

        $notifications = $query->paginate($perPage);

        $data = $notifications->map(function (DatabaseNotification $n) {
            $notificationData = $n->data;

            return [
                'id'         => $n->id,
                'type'       => $this->mapNotificationType($notificationData),
                'title'      => $this->getNotificationTitle($notificationData),
                'message'    => $notificationData['message'] ?? 'New notification',
                'url'        => $notificationData['url'] ?? null,
                'level'      => $notificationData['level'] ?? 'normal',
                'record_type'=> $notificationData['record_type'] ?? null,
                'record_id'  => $notificationData['record_id'] ?? null,
                'created_at' => $n->created_at->toIso8601String(),
                'read_at'    => optional($n->read_at)->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
                'has_more'     => $notifications->hasMorePages(),
                'unread_count' => $user->unreadNotifications()->count(),
            ]
        ]);
    }

    private function mapNotificationType($data)
    {
        $level = $data['level'] ?? 'normal';
        $recordType = $data['record_type'] ?? '';

        // Map based on record type or level
        if (str_contains($recordType, 'Order')) return 'order';
        if (str_contains($recordType, 'Payment')) return 'payment';

        return match($level) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'error',
            default => 'info'
        };
    }

    private function getNotificationTitle($data)
    {
        $level = $data['level'] ?? 'normal';
        $recordType = $data['record_type'] ?? '';

        if ($recordType) {
            $type = class_basename($recordType);
            return ucfirst($level) . ' - ' . $type . ' Update';
        }

        return ucfirst($level) . ' Notification';
    }

    public function markAsRead(string $id, Request $request)
    {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    public function markAsUnread(string $id, Request $request)
    {
        $request->user()->notifications()->whereKey($id)->update(['read_at' => null]);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function destroy(string $id, Request $request)
    {
        $request->user()->notifications()->whereKey($id)->delete();
        return response()->json(['success' => true]);
    }

    public function clearAll(Request $request)
    {
        $request->user()->notifications()->delete();
        return response()->json(['success' => true]);
    }
}

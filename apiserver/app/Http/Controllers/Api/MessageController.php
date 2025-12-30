<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MessageController extends Controller
{
    /**
     * Check if user has an active subscription (not regular user).
     * Only Member, Promoter, Advisor, Mentor can use messaging.
     */
    private function checkSubscriptionAccess(User $user): ?JsonResponse
    {
        // Check if user has an active subscription
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->whereDate('expires_at', '>', now())
            ->exists();

        if (! $activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'Messaging is available only for subscribed members. Please upgrade your subscription to access this feature.',
                'requires_subscription' => true,
            ], 403);
        }

        return null;
    }

    /**
     * Get all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check subscription access
        $accessCheck = $this->checkSubscriptionAccess($user);
        if ($accessCheck) {
            return $accessCheck;
        }

        $conversations = Conversation::query()
            ->forUser($user)
            ->with(['userOne', 'userTwo', 'latestMessage', 'admin'])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->whereNull('read_at')
                    ->where('sender_user_id', '!=', $user->id);
            }])
            ->orderByDesc('last_message_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $conversations->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Get broadcast messages from admin.
     */
    public function broadcasts(Request $request): JsonResponse
    {
        $user = $request->user();

        $broadcasts = Conversation::query()
            ->broadcast()
            ->with(['admin', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $broadcasts->items(),
            'meta' => [
                'current_page' => $broadcasts->currentPage(),
                'last_page' => $broadcasts->lastPage(),
                'per_page' => $broadcasts->perPage(),
                'total' => $broadcasts->total(),
            ],
        ]);
    }

    /**
     * Get a single conversation with messages.
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is part of this conversation
        if (! $conversation->is_broadcast) {
            if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }
        }

        // Mark messages as read
        $conversation->markAsReadFor($user);

        // Load messages
        $messages = $conversation->messages()
            ->with(['senderUser', 'senderAdmin'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 50));

        // Get other participant info
        $otherParticipant = $conversation->getOtherParticipant($user);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => [
                    'uuid' => $conversation->uuid,
                    'subject' => $conversation->subject,
                    'is_broadcast' => $conversation->is_broadcast,
                    'last_message_at' => $conversation->last_message_at,
                    'other_participant' => $otherParticipant ? [
                        'uuid' => $otherParticipant->uuid,
                        'name' => $otherParticipant->name,
                        'avatar_url' => $otherParticipant->avatar_url,
                    ] : null,
                    'admin' => $conversation->admin ? [
                        'name' => $conversation->admin->name,
                    ] : null,
                ],
                'messages' => $messages->items(),
            ],
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Start a new conversation or get existing one with a user.
     */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check subscription access
        $accessCheck = $this->checkSubscriptionAccess($user);
        if ($accessCheck) {
            return $accessCheck;
        }

        $request->validate([
            'recipient_uuid' => ['required', 'string', 'uuid'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $recipient = User::where('uuid', $request->input('recipient_uuid'))->first();

        if (! $recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found',
            ], 404);
        }

        if ($recipient->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot message yourself',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Find or create conversation
            $conversation = Conversation::findOrCreateBetween(
                $user,
                $recipient,
                $request->input('subject')
            );

            // Create message
            $message = $conversation->messages()->create([
                'sender_user_id' => $user->id,
                'body' => $request->input('message'),
                'type' => 'text',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message sent',
                'data' => [
                    'conversation_uuid' => $conversation->uuid,
                    'message' => $message,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
            ], 500);
        }
    }

    /**
     * Send a message in an existing conversation.
     */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check subscription access
        $accessCheck = $this->checkSubscriptionAccess($user);
        if ($accessCheck) {
            return $accessCheck;
        }

        $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'type' => ['nullable', 'string', 'in:text,image,file'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['string'],
        ]);

        // Check if user is part of this conversation
        if (! $conversation->is_broadcast) {
            if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }
        } else {
            // Users cannot reply to broadcast messages
            return response()->json([
                'success' => false,
                'message' => 'Cannot reply to broadcast messages',
            ], 400);
        }

        $message = $conversation->messages()->create([
            'sender_user_id' => $user->id,
            'body' => $request->input('message'),
            'type' => $request->input('type', 'text'),
            'attachments' => $request->input('attachments'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent',
            'data' => $message->load('senderUser'),
        ]);
    }

    /**
     * Delete a message (soft delete).
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        $user = $request->user();

        // Only sender can delete their own message
        if ($message->sender_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this message',
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted',
        ]);
    }

    /**
     * Get unread message count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $directUnread = Conversation::query()
            ->forUser($user)
            ->direct()
            ->get()
            ->sum(fn ($c) => $c->getUnreadCountFor($user));

        $broadcastCount = Conversation::query()
            ->broadcast()
            ->where('created_at', '>=', $user->created_at)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'direct_unread' => $directUnread,
                'broadcast_count' => $broadcastCount,
                'total' => $directUnread + $broadcastCount,
            ],
        ]);
    }

    /**
     * Mark all messages in a conversation as read.
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is part of this conversation
        if (! $conversation->is_broadcast) {
            if ($conversation->user_one_id !== $user->id && $conversation->user_two_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }
        }

        $conversation->markAsReadFor($user);

        return response()->json([
            'success' => true,
            'message' => 'Marked as read',
        ]);
    }

    /**
     * Get list of users the authenticated user can message.
     * Only users in Affiliate network (parent/children) can message each other.
     */
    public function availableRecipients(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->input('search');

        // Get users from Affiliate network (parent and direct referrals)
        $parentId = $user->genealogy?->parent_id;
        $childrenIds = $user->referrals()->pluck('id')->toArray();

        $availableUserIds = array_filter(array_merge([$parentId], $childrenIds));

        $query = User::query()
            ->whereIn('id', $availableUserIds)
            ->select(['uuid', 'name', 'mobile']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(20)->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(fn ($u) => [
                'uuid' => $u->uuid,
                'name' => $u->name,
                'mobile_masked' => substr($u->mobile, 0, 3).'****'.substr($u->mobile, -3),
                'avatar_url' => $u->avatar_url,
            ]),
        ]);
    }
}

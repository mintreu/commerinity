<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\HelpdeskTopicResource;
use App\Http\Resources\TicketConversationResource;
use App\Http\Resources\TicketResource;
use App\Models\HelpdeskConversation;
use App\Models\HelpdeskTopic;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->forUser($request->user()->id)
            ->with(['topic', 'user'])
            ->recent()
            ->get();

        return response()->json([
            'data' => TicketResource::collection($tickets),
        ]);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('helpdesk/attachments', 'public');
            $data['attachment'] = [Storage::url($path)];
        }

        $ticket = Ticket::create($data);
        $ticket->load(['topic', 'user']);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function show(Ticket $ticket, Request $request): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->load(['topic', 'user', 'conversations.authorable']);

        return response()->json([
            'data' => [
                'ticket' => new TicketResource($ticket),
                'conversations' => TicketConversationResource::collection($ticket->conversations),
            ],
        ]);
    }

    public function reply(ReplyTicketRequest $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $attachments = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('helpdesk/conversations', 'public');
                $attachments[] = Storage::url($path);
            }
        }

        $conversation = HelpdeskConversation::create([
            'ticket_id' => $ticket->id,
            'message' => $data['message'],
            'authorable_type' => get_class($request->user()),
            'authorable_id' => $request->user()->id,
            'attachment' => $attachments,
        ]);

        $conversation->load('authorable');

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => new TicketConversationResource($conversation),
        ], 201);
    }

    public function topics(): JsonResponse
    {
        $topics = HelpdeskTopic::query()
            ->active()
            ->tickable()
            ->ordered()
            ->get();

        return response()->json([
            'data' => HelpdeskTopicResource::collection($topics),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\HelpdeskTopicResource;
use App\Http\Resources\TicketConversationResource;
use App\Http\Resources\TicketResource;
use App\Models\Support\Helpdesk;
use App\Models\Support\HelpdeskConversation;
use App\Models\Support\HelpdeskTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Helpdesk::query()
            ->forUser($request->user())
            ->with(['topic', 'authorable'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => TicketResource::collection($tickets),
        ]);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['authorable_type'] = $request->user()->getMorphClass();
        $data['authorable_id'] = $request->user()->getKey();

        $ticket = Helpdesk::create($data);

        if ($request->hasFile('screenshot')) {
            $ticket->addMediaFromRequest('screenshot')->toMediaCollection('ticketAttachment');
        }

        $ticket->load(['topic', 'authorable']);

        return response()->json([
            'message' => 'Ticket created successfully',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function show(Helpdesk $ticket, Request $request): JsonResponse
    {
        if ($ticket->authorable_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->load(['topic', 'authorable', 'conversations.authorable']);

        return response()->json([
            'data' => [
                'ticket' => new TicketResource($ticket),
                'conversations' => TicketConversationResource::collection($ticket->conversations),
            ],
        ]);
    }

    public function reply(ReplyTicketRequest $request, Helpdesk $ticket): JsonResponse
    {
        if ($ticket->authorable_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $conversation = HelpdeskConversation::create([
            'helpdesk_id' => $ticket->id,
            'message' => $data['message'],
            'authorable_type' => $request->user()->getMorphClass(),
            'authorable_id' => $request->user()->getKey(),
            'source' => 'human',
            'is_internal' => false,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $conversation->addMedia($file)->toMediaCollection('ticketConversationAttachment');
            }
        }

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
            ->ticketable()
            ->ordered()
            ->get();

        return response()->json([
            'data' => HelpdeskTopicResource::collection($topics),
        ]);
    }
}

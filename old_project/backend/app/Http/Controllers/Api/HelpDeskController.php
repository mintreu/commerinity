<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\ReplyHelpdeskRequest;
use App\Http\Requests\Helpdesk\StoreHelpdeskRequest;
use App\Http\Resources\Helpdesk\HelpdeskConversationResource;
use App\Http\Resources\Helpdesk\HelpdeskResource;
use App\Http\Resources\Helpdesk\HelpdeskTopicResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskPriorityCast;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskStatusCast;
use Mintreu\LaravelHelpdesk\Models\Helpdesk;
use Mintreu\LaravelHelpdesk\Models\HelpdeskTopic;

class HelpDeskController extends Controller
{
    public function getTicketTopics()
    {
        return HelpdeskTopicResource::collection(
            HelpdeskTopic::active()->ticketable()->orderBy('order')->get()
        );
    }

    public function getFaqTopics()
    {
        return HelpdeskTopicResource::collection(
            HelpdeskTopic::active()->nonTicketable()->orderBy('order')->get()
        );
    }

    public function getAllTickets(Request $request)
    {
        $tickets = Helpdesk::query()
            ->whereMorphedTo('authorable', $request->user())
            ->latest('created_at')
            ->with('topic')
            ->get();

        return HelpdeskResource::collection($tickets);
    }

    public function viewTicket(Helpdesk $helpdesk)
    {
        $helpdesk->load([
            'topic',
            'conversations' => fn($q) => $q->latest('created_at'),
            'conversations.authorable',
            'conversations.authorable.media' =>fn($q) => $q->where('collection_name','avatar'),
            'conversations.media' =>fn($q) => $q->where('collection_name','ticketConversationAttachment'),
            'authorable'
        ]);



        return response()->json([
            'ticket'        => new HelpdeskResource($helpdesk),
            'conversations' => HelpdeskConversationResource::collection($helpdesk->conversations),
        ]);
    }

    public function storeTicket(StoreHelpdeskRequest $request)
    {
        $topic = HelpdeskTopic::where('slug', $request->topic_slug)->firstOrFail();

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $topic,$user) {
            $ticket = $user->tickets()->create([
                'title'       => $request->input('title'),
                'description' => $request->input('description'),
                'priority'    => HelpdeskPriorityCast::from($request->input('priority')),
                'status'      => HelpdeskStatusCast::OPEN,
                'topic_id'    => $topic->id,
            ]);

            if ($request->hasFile('screenshot')) {
                $ticket->addMediaFromRequest('screenshot')->toMediaCollection('ticketAttachment');
            }

            return $ticket;
        });

        return (new HelpdeskResource($ticket->load('topic')))
            ->additional(['message' => 'Ticket created successfully'])
            ->response();
    }

    public function reply(ReplyHelpdeskRequest $request, Helpdesk $helpdesk)
    {
        // Create via polymorphic relation on the authenticated user.
        // authorable_type/id are set automatically by Eloquent when using the relation.
        $conversation = $request->user()->ticketConversations()->create([
            'helpdesk_id' => $helpdesk->id,
            'message'     => $request->string('message'),

        ]);

        // Attach images to the conversation's media collection
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $conversation
                    ->addMedia($file)
                    ->toMediaCollection('ticketConversationAttachment');
            }
        }

        // Refresh with relations expected by your Resource (author morph is 'authorable' in the model)
        return (new \App\Http\Resources\Helpdesk\HelpdeskConversationResource(
            $conversation->fresh(['media', 'authorable'])
        ))
            ->additional(['success' => true, 'message' => 'Reply added successfully'])
            ->response()
            ->setStatusCode(201);
    }



    public function uploadAttachment(Request $request, Helpdesk $helpdesk)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10 MB
        ]);

        $media = $helpdesk->addMediaFromRequest('file')->toMediaCollection('ticketAttachment');

        return response()->json([
            'message'  => 'Attachment uploaded successfully',
            'media_id' => $media->id,
            'file'     => [
                'name' => $media->file_name,
                'size' => $media->size,
                'mime' => $media->mime_type,
            ],
        ], 201);
    }
}

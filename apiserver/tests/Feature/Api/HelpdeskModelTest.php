<?php

declare(strict_types=1);

use App\Casts\HelpdeskPriorityCast;
use App\Casts\HelpdeskStatusCast;
use App\Models\Admin;
use App\Models\Helpdesk\Helpdesk;
use App\Models\Helpdesk\HelpdeskConversation;
use App\Models\Helpdesk\HelpdeskFaq;
use App\Models\Helpdesk\HelpdeskTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// HELPDESK TOPIC TESTS
// ========================================

describe('HelpdeskTopic Model', function () {
    it('can create a topic', function () {
        $topic = HelpdeskTopic::factory()->create([
            'name' => 'Test Topic',
            'slug' => 'test-topic',
        ]);

        expect($topic->exists)->toBeTrue();
        expect($topic->name)->toBe('Test Topic');
        expect($topic->slug)->toBe('test-topic');
    });

    it('generates slug automatically if not provided', function () {
        $topic = HelpdeskTopic::factory()->create([
            'name' => 'Auto Slug Test',
            'slug' => null,
        ]);

        expect($topic->slug)->toBe('auto-slug-test');
    });

    it('has tickable and faq-only scopes', function () {
        HelpdeskTopic::factory()->create(['tickable' => true]);
        HelpdeskTopic::factory()->create(['tickable' => false]);

        expect(HelpdeskTopic::ticketable()->count())->toBe(1);
        expect(HelpdeskTopic::nonTicketable()->count())->toBe(1);
    });

    it('has active scope', function () {
        HelpdeskTopic::factory()->create(['active' => true]);
        HelpdeskTopic::factory()->create(['active' => false]);

        expect(HelpdeskTopic::active()->count())->toBe(1);
    });

    it('has tickets relationship', function () {
        $topic = HelpdeskTopic::factory()->create();
        $user = User::factory()->create();

        Helpdesk::factory()->forTopic($topic)->forUser($user)->create();

        expect($topic->tickets)->toHaveCount(1);
    });

    it('has faqs relationship', function () {
        $topic = HelpdeskTopic::factory()->create();
        HelpdeskFaq::factory()->forTopic($topic)->count(3)->create();

        expect($topic->faqs)->toHaveCount(3);
    });
});

// ========================================
// HELPDESK (TICKET) TESTS
// ========================================

describe('Helpdesk Model', function () {
    it('can create a ticket', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create([
            'title' => 'Test Ticket',
        ]);

        expect($ticket->exists)->toBeTrue();
        expect($ticket->title)->toBe('Test Ticket');
        expect($ticket->authorable_id)->toBe($user->id);
    });

    it('generates uuid with prefix automatically', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        expect($ticket->uuid)->toStartWith('TICKET-');
        expect(strlen($ticket->uuid))->toBe(22); // TICKET-YYMMDD-XXXXXXXX (7+1+6+1+8=22)
    });

    it('sets default status to open', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create([
            'status' => null,
        ]);

        expect($ticket->status)->toBe(HelpdeskStatusCast::Open);
    });

    it('has priority cast', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->urgent()->create();

        expect($ticket->priority)->toBe(HelpdeskPriorityCast::Urgent);
        expect($ticket->priority->getLabel())->toBe(__('helpdesk.priority.urgent'));
    });

    it('can mark ticket as resolved', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->open()->create();
        $ticket->resolve();

        expect($ticket->fresh()->status)->toBe(HelpdeskStatusCast::Resolved);
        expect($ticket->fresh()->resolved_at)->not->toBeNull();
    });

    it('can close and reopen ticket', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->open()->create();

        $ticket->close();
        expect($ticket->fresh()->status)->toBe(HelpdeskStatusCast::Closed);
        expect($ticket->fresh()->closed_at)->not->toBeNull();

        $ticket->reopen();
        expect($ticket->fresh()->status)->toBe(HelpdeskStatusCast::Open);
        expect($ticket->fresh()->closed_at)->toBeNull();
    });

    it('has active scope for open tickets', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        Helpdesk::factory()->forUser($user)->forTopic($topic)->open()->create();
        Helpdesk::factory()->forUser($user)->forTopic($topic)->closed()->create();

        expect(Helpdesk::active()->count())->toBe(1);
    });

    it('has conversations relationship', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();
        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        HelpdeskConversation::factory()->forTicket($ticket)->fromUser($user)->count(5)->create();

        expect($ticket->conversations)->toHaveCount(5);
    });

    it('can rate satisfaction after resolution', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->resolved()->create();
        $ticket->rateSatisfaction(5, 'Excellent support!');

        $ticket->refresh();
        expect($ticket->satisfaction_rating)->toBe(5);
        expect($ticket->satisfaction_feedback)->toBe('Excellent support!');
    });
});

// ========================================
// HELPDESK CONVERSATION TESTS
// ========================================

describe('HelpdeskConversation Model', function () {
    it('can create a conversation message', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();
        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        $conversation = HelpdeskConversation::factory()
            ->forTicket($ticket)
            ->fromUser($user)
            ->withMessage('Test message')
            ->create();

        expect($conversation->exists)->toBeTrue();
        expect($conversation->message)->toBe('Test message');
        expect($conversation->source)->toBe('human');
    });

    it('can be from bot', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();
        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        $conversation = HelpdeskConversation::factory()
            ->forTicket($ticket)
            ->fromBot()
            ->create();

        expect($conversation->source)->toBe('bot');
        expect($conversation->is_from_bot)->toBeTrue();
        expect($conversation->authorable_id)->toBeNull();
    });

    it('can be internal note', function () {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();
        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        $conversation = HelpdeskConversation::factory()
            ->forTicket($ticket)
            ->fromAdmin($admin)
            ->internal()
            ->create();

        expect($conversation->is_internal)->toBeTrue();
        expect(HelpdeskConversation::internal()->count())->toBe(1);
        expect(HelpdeskConversation::public()->count())->toBe(0);
    });

    it('updates ticket last_activity_at on creation', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();
        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        $originalActivity = $ticket->last_activity_at;

        // Wait a bit and create conversation
        sleep(1);
        HelpdeskConversation::factory()->forTicket($ticket)->fromUser($user)->create();

        $ticket->refresh();
        expect($ticket->last_activity_at->gt($originalActivity))->toBeTrue();
    });
});

// ========================================
// HELPDESK FAQ TESTS
// ========================================

describe('HelpdeskFaq Model', function () {
    it('can create a FAQ', function () {
        $topic = HelpdeskTopic::factory()->create();

        $faq = HelpdeskFaq::factory()->forTopic($topic)->create([
            'question' => 'How do I reset my password?',
        ]);

        expect($faq->exists)->toBeTrue();
        expect($faq->question)->toBe('How do I reset my password?');
    });

    it('generates url slug from question', function () {
        $topic = HelpdeskTopic::factory()->create();

        $faq = HelpdeskFaq::factory()->forTopic($topic)->create([
            'question' => 'What is the refund policy?',
            'url' => null,
        ]);

        expect($faq->url)->toBe('what-is-the-refund-policy');
    });

    it('has morphable audience relationship', function () {
        $topic = HelpdeskTopic::factory()->create();
        $user = User::factory()->create();

        // Public FAQ (no specific audience)
        $publicFaq = HelpdeskFaq::factory()->forTopic($topic)->forEveryone()->create();
        expect($publicFaq->audience_type)->toBeNull();
        expect($publicFaq->audience_id)->toBeNull();

        // User-specific FAQ
        $userFaq = HelpdeskFaq::factory()->forTopic($topic)->forAudience(User::class, $user->id)->create();
        expect($userFaq->audience_type)->toBe(User::class);
        expect($userFaq->audience_id)->toBe($user->id);
        expect($userFaq->audience)->toBeInstanceOf(User::class);
    });

    it('can filter by audience', function () {
        $topic = HelpdeskTopic::factory()->create();
        $user = User::factory()->create();

        // Create public FAQ and user-specific FAQ
        HelpdeskFaq::factory()->forTopic($topic)->forEveryone()->create();
        HelpdeskFaq::factory()->forTopic($topic)->forAudience(User::class, $user->id)->create();
        HelpdeskFaq::factory()->forTopic($topic)->forEveryone()->create();

        // Guest should only see public FAQs (2)
        expect(HelpdeskFaq::forAudience()->count())->toBe(2);

        // User should see public + their specific FAQs (3)
        expect(HelpdeskFaq::forAudience(User::class, $user->id)->count())->toBe(3);
    });

    it('can increment views', function () {
        $topic = HelpdeskTopic::factory()->create();
        $faq = HelpdeskFaq::factory()->forTopic($topic)->create(['views' => 0]);

        $faq->incrementViews();
        $faq->incrementViews();

        expect($faq->fresh()->views)->toBe(2);
    });

    it('can track helpful feedback', function () {
        $topic = HelpdeskTopic::factory()->create();
        $faq = HelpdeskFaq::factory()->forTopic($topic)->create([
            'helpful_count' => 0,
            'not_helpful_count' => 0,
        ]);

        $faq->markHelpful();
        $faq->markHelpful();
        $faq->markNotHelpful();

        $faq->refresh();
        expect($faq->helpful_count)->toBe(2);
        expect($faq->not_helpful_count)->toBe(1);
        expect($faq->helpful_percentage)->toBe(66.7);
    });

    it('can search by question and answer', function () {
        $topic = HelpdeskTopic::factory()->create();

        HelpdeskFaq::factory()->forTopic($topic)->create([
            'question' => 'How to setup wallet?',
            'answer' => 'Follow these steps...',
        ]);
        HelpdeskFaq::factory()->forTopic($topic)->create([
            'question' => 'What is commission?',
            'answer' => 'Commission is earnings...',
        ]);

        expect(HelpdeskFaq::search('wallet')->count())->toBe(1);
        expect(HelpdeskFaq::search('earnings')->count())->toBe(1);
    });
});

// ========================================
// USER HELPDESK RELATIONSHIP TESTS
// ========================================

describe('User Helpdesk Relationships', function () {
    it('user can have multiple tickets', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        Helpdesk::factory()->forUser($user)->forTopic($topic)->count(3)->create();

        expect($user->tickets)->toHaveCount(3);
    });

    it('user can have multiple ticket conversations', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();
        $ticket = Helpdesk::factory()->forUser($user)->forTopic($topic)->create();

        HelpdeskConversation::factory()->forTicket($ticket)->fromUser($user)->count(5)->create();

        expect($user->ticketConversations)->toHaveCount(5);
    });

    it('user can check active tickets count', function () {
        $user = User::factory()->create();
        $topic = HelpdeskTopic::factory()->create();

        Helpdesk::factory()->forUser($user)->forTopic($topic)->open()->count(2)->create();
        Helpdesk::factory()->forUser($user)->forTopic($topic)->closed()->create();

        expect($user->active_tickets_count)->toBe(2);
        expect($user->total_tickets_count)->toBe(3);
    });
});

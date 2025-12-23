# Helpdesk & FAQ System Implementation Plan

## Overview

Enterprise-grade helpdesk and FAQ system for commerinity_pro with support tickets, real-time chat, FAQ management, and Filament admin panel.

## System Architecture

### Database Schema

```
helpdesk_topics (id, name, slug, description, icon, tickable, active, order, timestamps)
    │
    ├── helpdesks [tickets] (id, uuid, title, description, priority, status, topic_id,
    │                        authorable_type, authorable_id, resolved_at, timestamps)
    │       │
    │       └── helpdesk_conversations (id, helpdesk_id, message, authorable_type,
    │                                    authorable_id, is_internal, timestamps)
    │
    └── helpdesk_faqs (id, url, question, answer, topic_id, active, order,
                       views, helpful_count, not_helpful_count, tags, timestamps)
```

### Enums

```php
HelpdeskStatusCast: open, awaiting_reply, in_progress, resolved, closed
HelpdeskPriorityCast: low, medium, high, urgent
```

### Models

| Model | Purpose |
|-------|---------|
| `HelpdeskTopic` | Categories for tickets & FAQs |
| `Helpdesk` | Support tickets |
| `HelpdeskConversation` | Chat messages |
| `HelpdeskFaq` | FAQ articles |

## API Endpoints

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/helpdesk/topics` | All active topics |
| GET | `/api/helpdesk/faqs` | FAQ listing with topic filter |
| GET | `/api/helpdesk/faqs/{url}` | Single FAQ article |
| POST | `/api/helpdesk/faqs/{url}/feedback` | Mark helpful/not helpful |

### Authenticated Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/helpdesk/topics/ticketable` | Topics that allow tickets |
| GET | `/api/helpdesk/tickets` | User's tickets (paginated, filterable) |
| POST | `/api/helpdesk/tickets` | Create new ticket |
| GET | `/api/helpdesk/tickets/{uuid}` | Ticket detail with conversations |
| POST | `/api/helpdesk/tickets/{uuid}/reply` | Add message to ticket |
| POST | `/api/helpdesk/tickets/{uuid}/close` | Close own ticket |
| GET | `/api/helpdesk/tickets/{uuid}/conversations` | Paginated conversations |

## Frontend Pages

### FAQ Section (Public)
```
/support                    - Main support hub (topics + popular FAQs)
/support/faqs               - All FAQs with search & topic filter
/support/faqs/[url]         - Single FAQ article with feedback
```

### Ticket System (Authenticated)
```
/support/tickets            - My tickets list with filters
/support/tickets/create     - Create new ticket form
/support/tickets/[uuid]     - Ticket detail with chat interface
```

## Filament Admin Panel

### Resources
1. **HelpdeskTopicResource** - CRUD for topics
   - Reorderable list
   - Toggle active/tickable status
   - Icon selection

2. **HelpdeskResource** - Ticket management
   - List with status/priority badges
   - Bulk actions (resolve, close)
   - Conversation timeline view
   - Internal notes support
   - Assignment to admins (future)

3. **HelpdeskFaqResource** - FAQ management
   - Rich text editor for answers
   - Tag management
   - View/feedback stats
   - Reorderable per topic

### Widgets
- **TicketStatsWidget** - Open/pending/resolved counts
- **RecentTicketsWidget** - Latest 5 tickets needing attention

## Implementation Phases

### Phase 1: Backend Foundation
1. Create migrations (4 tables)
2. Create enum casts (HelpdeskStatusCast, HelpdeskPriorityCast)
3. Create models with relationships
4. Create factories and seeders
5. Write Pest tests for models

### Phase 2: API Layer
1. Create Form Requests (validation)
2. Create API Resources (responses)
3. Create HelpdeskService class
4. Create HelpdeskController
5. Define routes
6. Write Pest tests for API

### Phase 3: Filament Admin
1. Create HelpdeskTopicResource
2. Create HelpdeskResource (tickets)
3. Create HelpdeskFaqResource
4. Create dashboard widgets
5. Test admin functionality

### Phase 4: Frontend (Nuxt)
1. Create composable `useHelpdesk.ts`
2. Create support hub page
3. Create FAQ pages (list, detail)
4. Create ticket pages (list, create, detail)
5. Implement real-time chat UI

## Professional Features

### Ticket System
- [x] UUID-based ticket reference (TICKET-YYMMDD-XXXXX)
- [x] Priority levels with colors
- [x] Status workflow (open → in_progress → resolved → closed)
- [ ] Auto-close stale tickets (configurable days)
- [ ] Email notifications on status change
- [ ] File attachments (Spatie Media Library)
- [ ] Internal notes (admin-only messages)
- [ ] Canned responses for admins

### FAQ System
- [x] Topic-based organization
- [x] URL-friendly slugs
- [ ] View counter
- [ ] Helpful/Not helpful feedback
- [ ] Related FAQs
- [ ] Search with keyword matching
- [ ] Tag-based filtering

### UX Improvements
- [ ] Search before creating ticket (suggest FAQs)
- [ ] Ticket satisfaction rating after resolution
- [ ] Knowledge base integration
- [ ] Quick actions (close, reopen)

## Tech Stack Alignment

| Component | Technology |
|-----------|------------|
| Backend | Laravel 12, PHP 8.3 |
| Admin | Filament v4 |
| API Auth | Sanctum v4 |
| Frontend | Nuxt 4, Nuxt UI v4 |
| Testing | Pest v4 |
| Files | Spatie Media Library |

## Estimated Files

### Backend (~25 files)
```
app/
├── Casts/
│   ├── HelpdeskStatusCast.php
│   └── HelpdeskPriorityCast.php
├── Models/
│   ├── HelpdeskTopic.php
│   ├── Helpdesk.php
│   ├── HelpdeskConversation.php
│   └── HelpdeskFaq.php
├── Http/
│   ├── Controllers/Api/HelpdeskController.php
│   ├── Requests/Helpdesk/
│   │   ├── StoreTicketRequest.php
│   │   └── ReplyTicketRequest.php
│   └── Resources/Helpdesk/
│       ├── HelpdeskTopicResource.php
│       ├── HelpdeskResource.php
│       ├── HelpdeskConversationResource.php
│       └── HelpdeskFaqResource.php
├── Services/
│   └── HelpdeskService.php
└── Filament/Resources/
    ├── HelpdeskTopicResource.php
    ├── HelpdeskResource.php
    ├── HelpdeskFaqResource.php
    └── Widgets/
        ├── TicketStatsWidget.php
        └── RecentTicketsWidget.php

database/
├── migrations/
│   ├── xxxx_create_helpdesk_topics_table.php
│   ├── xxxx_create_helpdesks_table.php
│   ├── xxxx_create_helpdesk_conversations_table.php
│   └── xxxx_create_helpdesk_faqs_table.php
└── factories/
    ├── HelpdeskTopicFactory.php
    ├── HelpdeskFactory.php
    ├── HelpdeskConversationFactory.php
    └── HelpdeskFaqFactory.php

tests/Feature/Api/HelpdeskTest.php
```

### Frontend (~8 files)
```
client/app/
├── composables/
│   └── useHelpdesk.ts
└── pages/
    └── support/
        ├── index.vue           # Support hub
        ├── faqs/
        │   ├── index.vue       # FAQ listing
        │   └── [url].vue       # FAQ detail
        └── tickets/
            ├── index.vue       # My tickets
            ├── create.vue      # New ticket
            └── [uuid].vue      # Ticket detail + chat
```

## Notes

- Follow existing patterns from RecruitmentSystem
- Use Nuxt UI v4 components throughout
- All API calls via `useSanctumFetch`
- Filament enums must implement HasLabel, HasColor, HasIcon
- Test-first development approach

---

---

## Future Scope: AI Chatbot Plugin (NOT NOW)

**Status**: Scope Reserved - Build AFTER helpdesk complete

**Concept**: Floating chatbot widget as SaaS for external websites
- HuggingFace Spaces integration ($9/mo or free tier)
- LoRA fine-tuning on client website/DB data
- Subscription-based service for external clients
- Integrates with helpdesk for ticket escalation

**Architecture Hooks to Leave**:
1. `Helpdesk` model - add `chatbot_session_id` nullable field (for escalated chats)
2. `HelpdeskConversation` - support `source` field (human/bot)
3. API structure - keep `/api/helpdesk/*` prefix extensible
4. Widget position config in topics (for future floating button)

**Implementation**: Separate phase after core helpdesk is production-ready

---

**Status**: Plan Ready - Awaiting User Confirmation

**Next Action**: User confirms plan, then begin Phase 1 (Backend Foundation)

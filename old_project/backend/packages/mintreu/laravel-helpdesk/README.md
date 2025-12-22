# `mintreu/laravel-helpdesk`

## 1. Package Overview

The `mintreu/laravel-helpdesk` package provides a foundational helpdesk system for Laravel applications. It enables the creation and management of support tickets, facilitates conversations between users and support staff, and allows for the organization of frequently asked questions (FAQs) and helpdesk topics.

This package is designed to be integrated into existing Laravel applications, offering a structured way to handle customer support interactions.

### Core Features

-   **Ticket Management:** Create, track, and manage support tickets with attributes like title, description, priority, and status.
-   **Conversations:** Facilitate communication within tickets, allowing multiple messages and attachments.
-   **FAQ System:** Organize common questions and answers into topics for self-service support.
-   **Categorization:** Group tickets and FAQs using `HelpdeskTopic` models.
-   **Polymorphic Relationships:** Link tickets and conversations to any user model (e.g., `User`, `Admin`, `Staff`).
-   **Media Attachments:** Support for attaching files to tickets and conversations using `spatie/laravel-medialibrary`.
-   **`HasSupportTicket` Trait:** A convenient trait to easily add helpdesk capabilities to any Eloquent model.

## 2. Architecture and Data Model

The package is built around four primary Eloquent models:

-   **`HelpdeskTopic`:** Categorizes tickets and FAQs. It has a `tickable` attribute to distinguish between topics meant for tickets and those for FAQs.
-   **`Helpdesk`:** Represents a single support ticket. It belongs to a `HelpdeskTopic` and has a polymorphic `authorable` relationship to the model that created it.
-   **`HelpdeskConversation`:** Represents a message or reply within a `Helpdesk` ticket. It also has a polymorphic `authorable` relationship to the message's author.
-   **`HelpdeskFaq`:** Stores frequently asked questions, each belonging to a `HelpdeskTopic`.

**Relationship Diagram:**

```
+-----------------+       +------------+       +----------------------+
|  HelpdeskTopic  |<-----|  Helpdesk  |------>| HelpdeskConversation |
+-----------------+       +------------+       +----------------------+
        ^                       ^                      ^
        |                       |                      |
        |                       | (authorable)         | (authorable)
        |                       |                      |
+-----------------+       +-------------+       +-------------+
|   HelpdeskFaq   |       |  Your Model |       |  Your Model |
| (e.g., User)|       | (e.g., User)|
+-----------------+       +-------------+       +-------------+
```

## 3. Installation

This package is a core component of the Commerinity project and is installed as a local path repository. To install it in another project, you would typically run:

```bash
composer require mintreu/laravel-helpdesk
```

You would then publish and run the migrations:

```bash
php artisan vendor:publish --tag="laravel-helpdesk-migrations"
php artisan migrate
```

## 4. Usage

### The `HasSupportTicket` Trait

To enable a model (e.g., `User`, `Admin`) to create and manage support tickets, use the `HasSupportTicket` trait.

```php
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelHelpdesk\Traits\HasSupportTicket;

class User extends Model
{
    use HasSupportTicket;

    // ...
}
```

This trait provides the following relationships and methods:

-   **`tickets()`:** A `morphMany` relationship to retrieve all tickets created by this model.
-   **`ticketConversations()`:** A `morphMany` relationship to retrieve all conversations authored by this model across all tickets.
-   **`ticketConversationsFor(Helpdesk|int $ticket)`:** A helper method to retrieve conversations authored by this model for a specific ticket.

**Example Usage:**

```php
$user = User::find(1);

// Create a new ticket
$ticket = $user->tickets()->create([
    'title' => 'My printer is not working',
    'description' => 'I need help with my printer.',
    'priority' => 'high',
    'status' => 'open',
    'topic_id' => HelpdeskTopic::first()->id,
]);

// Add a conversation to the ticket
$user->ticketConversationsFor($ticket)->create([
    'message' => 'I have tried restarting it.',
    'helpdesk_id' => $ticket->id,
]);

// Get all tickets for the user
$tickets = $user->tickets;
```

## 5. Review

### Strengths:
-   **Comprehensive Data Model:** The package provides a solid and well-structured data model for managing helpdesk tickets, conversations, topics, and FAQs.
-   **Flexible User Association:** The use of polymorphic `authorable` relationships allows any model to interact with the helpdesk system, which is highly flexible.
-   **Media Attachments:** Integration with `spatie/laravel-medialibrary` is a valuable feature for users to provide context (e.g., screenshots) with their tickets and replies.
-   **Custom Casts:** The use of `HelpdeskPriorityCast` and `HelpdeskStatusCast` promotes type safety and consistency for critical ticket attributes.

### Weaknesses:
-   **Missing Tests:** The absence of a visible test suite is a major concern for a system that handles customer support. Without tests, the reliability and correctness of the system cannot be guaranteed.
-   **Lack of Workflow Automation:** The package provides the data models but lacks crucial workflow features such as ticket assignment, escalation, and automated notifications (email, in-app, push) for ticket events.
-   **No Email Integration:** A fundamental feature for most helpdesk systems, allowing users to create and reply to tickets via email, is not apparent.
-   **Limited Reporting/Analytics:** There are no built-in features for tracking key helpdesk metrics like response times, resolution rates, or ticket volume.

## 6. Recommendations for Improvement

1.  **Implement a Robust Test Suite:**
    -   **Unit Tests:** Write extensive unit tests for all models, their relationships, scopes, and custom methods (e.g., `Helpdesk::isOpen()`, `Helpdesk::markAs()`).
    -   **Feature Tests:** Create feature tests for the `HasSupportTicket` trait, covering the creation and retrieval of tickets and conversations, including media attachments.
    -   **Workflow Tests:** Develop tests that simulate the entire lifecycle of a ticket, from creation through multiple replies to resolution, ensuring all states and transitions work correctly.

2.  **Enhance with Workflow Automation and Key Features:**
    -   **Ticket Assignment:** Implement functionality for assigning tickets to specific support agents, either manually or automatically based on topic, priority, or agent availability.
    -   **Escalation Rules:** Add a system for escalating tickets that exceed predefined response or resolution times.
    -   **Notification System:** Integrate a comprehensive notification system (email, in-app, push) to alert users about ticket updates and support staff about new tickets or replies.
    -   **Email Integration:** Develop the ability to create tickets from incoming emails and allow users to reply to tickets directly via email.
    -   **SLA Management:** Implement Service Level Agreement (SLA) tracking to monitor and enforce response and resolution targets.
    -   **Reporting and Analytics:** Build features to generate reports and analytics on helpdesk performance, such as ticket volume, average response/resolution times, and agent productivity.
    -   **Filament Resources:** Create dedicated Filament resources for `Helpdesk`, `HelpdeskConversation`, `HelpdeskTopic`, and `HelpdeskFaq` to provide a full-featured administrative interface for managing the helpdesk.
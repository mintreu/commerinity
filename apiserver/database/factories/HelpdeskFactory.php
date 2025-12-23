<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\HelpdeskPriorityCast;
use App\Casts\HelpdeskStatusCast;
use App\Models\Admin;
use App\Models\Helpdesk\Helpdesk;
use App\Models\Helpdesk\HelpdeskTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Helpdesk\Helpdesk>
 */
class HelpdeskFactory extends Factory
{
    protected $model = Helpdesk::class;

    public function definition(): array
    {
        $prefix = config('helpdesk.ticket.prefix', 'TICKET');

        return [
            'uuid' => $prefix.'-'.now()->format('ymd').'-'.strtoupper(Str::random(8)),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'priority' => fake()->randomElement(HelpdeskPriorityCast::cases()),
            'status' => HelpdeskStatusCast::Open,
            'topic_id' => HelpdeskTopic::factory(),
            'authorable_type' => User::class,
            'authorable_id' => User::factory(),
            'assigned_to' => null,
            'chatbot_session_id' => null,
            'chatbot_context' => null,
            'resolved_at' => null,
            'closed_at' => null,
            'last_activity_at' => now(),
            'satisfaction_rating' => null,
            'satisfaction_feedback' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'authorable_type' => User::class,
            'authorable_id' => $user->id,
        ]);
    }

    public function forAdmin(Admin $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'authorable_type' => Admin::class,
            'authorable_id' => $admin->id,
        ]);
    }

    public function forTopic(HelpdeskTopic $topic): static
    {
        return $this->state(fn (array $attributes) => [
            'topic_id' => $topic->id,
        ]);
    }

    public function assignedTo(Admin $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $admin->id,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HelpdeskStatusCast::Open,
        ]);
    }

    public function awaitingReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HelpdeskStatusCast::AwaitingReply,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HelpdeskStatusCast::InProgress,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HelpdeskStatusCast::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HelpdeskStatusCast::Closed,
            'closed_at' => now(),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => HelpdeskPriorityCast::Low,
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => HelpdeskPriorityCast::Medium,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => HelpdeskPriorityCast::High,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => HelpdeskPriorityCast::Urgent,
        ]);
    }

    public function fromChatbot(?string $sessionId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'chatbot_session_id' => $sessionId ?? 'chat_'.Str::random(16),
            'chatbot_context' => [
                'messages' => [
                    ['role' => 'user', 'content' => 'Initial user message'],
                    ['role' => 'bot', 'content' => 'Bot response'],
                ],
                'escalated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function rated(int $rating = 5, ?string $feedback = null): static
    {
        return $this->state(fn (array $attributes) => [
            'satisfaction_rating' => $rating,
            'satisfaction_feedback' => $feedback ?? ($rating >= 4 ? 'Great support!' : 'Could be better.'),
        ]);
    }

    public function withConversations(int $count = 3): static
    {
        return $this->has(
            \App\Models\Helpdesk\HelpdeskConversation::factory()->count($count),
            'conversations'
        );
    }
}

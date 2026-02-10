<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Support\Helpdesk;
use App\Models\Support\HelpdeskConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Support\HelpdeskConversation>
 */
class HelpdeskConversationFactory extends Factory
{
    protected $model = HelpdeskConversation::class;

    public function definition(): array
    {
        return [
            'helpdesk_id' => Helpdesk::factory(),
            'message' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'authorable_type' => User::class,
            'authorable_id' => User::factory(),
            'source' => 'human',
            'is_internal' => false,
            'bot_metadata' => null,
        ];
    }

    public function forTicket(Helpdesk $ticket): static
    {
        return $this->state(fn (array $attributes) => [
            'helpdesk_id' => $ticket->id,
        ]);
    }

    public function fromUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'authorable_type' => User::class,
            'authorable_id' => $user->id,
            'source' => 'human',
        ]);
    }

    public function fromAdmin(Admin $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'authorable_type' => Admin::class,
            'authorable_id' => $admin->id,
            'source' => 'human',
        ]);
    }

    public function fromBot(): static
    {
        return $this->state(fn (array $attributes) => [
            'authorable_type' => null,
            'authorable_id' => null,
            'source' => 'bot',
            'bot_metadata' => [
                'model' => 'mistral-7b',
                'confidence' => fake()->randomFloat(2, 0.7, 0.99),
                'response_time_ms' => fake()->numberBetween(100, 2000),
            ],
        ]);
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => true,
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => false,
        ]);
    }

    public function withMessage(string $message): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $message,
        ]);
    }
}


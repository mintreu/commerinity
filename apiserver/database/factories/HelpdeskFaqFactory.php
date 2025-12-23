<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Helpdesk\HelpdeskFaq;
use App\Models\Helpdesk\HelpdeskTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Helpdesk\HelpdeskFaq>
 */
class HelpdeskFaqFactory extends Factory
{
    protected $model = HelpdeskFaq::class;

    public function definition(): array
    {
        $question = fake()->sentence(8).'?';

        return [
            'url' => Str::slug(Str::limit($question, 50, '')),
            'question' => $question,
            'answer' => fake()->paragraphs(3, true),
            'topic_id' => HelpdeskTopic::factory(),
            'audience_type' => null, // Public by default
            'audience_id' => null,
            'active' => true,
            'order' => fake()->numberBetween(0, 20),
            'views' => fake()->numberBetween(0, 1000),
            'helpful_count' => fake()->numberBetween(0, 100),
            'not_helpful_count' => fake()->numberBetween(0, 20),
            'tags' => fake()->randomElements(['account', 'payment', 'wallet', 'security', 'mobile', 'subscription', 'commission'], 3),
            'keywords' => fake()->words(5),
        ];
    }

    public function forTopic(HelpdeskTopic $topic): static
    {
        return $this->state(fn (array $attributes) => [
            'topic_id' => $topic->id,
        ]);
    }

    public function forAudience(string $audienceType, int $audienceId): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => $audienceType,
            'audience_id' => $audienceId,
        ]);
    }

    public function forEveryone(): static
    {
        return $this->state(fn (array $attributes) => [
            'audience_type' => null,
            'audience_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => fake()->numberBetween(500, 5000),
            'helpful_count' => fake()->numberBetween(50, 500),
        ]);
    }

    public function unpopular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => fake()->numberBetween(0, 10),
            'helpful_count' => 0,
            'not_helpful_count' => 0,
        ]);
    }

    public function withQuestion(string $question): static
    {
        return $this->state(fn (array $attributes) => [
            'question' => $question,
            'url' => Str::slug(Str::limit($question, 50, '')),
        ]);
    }

    public function withAnswer(string $answer): static
    {
        return $this->state(fn (array $attributes) => [
            'answer' => $answer,
        ]);
    }

    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    public function withKeywords(array $keywords): static
    {
        return $this->state(fn (array $attributes) => [
            'keywords' => $keywords,
        ]);
    }
}

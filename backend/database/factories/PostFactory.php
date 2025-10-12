<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(8);

        return [
            'name' => $name,
            'url' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(
                collect(PublishableStatusCast::cases())
                    ->map(fn($case) => $case->value)
                    ->toArray()
            ),
//            'status_feedback' => fake()->boolean(40)
//                ? fake()->sentence()
//                : null,
        ];
    }


    /** State for draft posts */
    public function draft(): static
    {
        return $this->state(fn() => [
            'status' => PublishableStatusCast::DRAFT,
        ]);
    }

    /** State for published posts */
    public function published(): static
    {
        return $this->state(fn() => [
            'status' => PublishableStatusCast::PUBLISHED,
        ]);
    }

    /** State for rejected posts */
    public function rejected(): static
    {
        return $this->state(fn() => [
            'status' => PublishableStatusCast::REJECTED,
            'status_feedback' => 'Your post was rejected by the reviewer.',
        ]);
    }

    /** State for pending review */
    public function pending(): static
    {
        return $this->state(fn() => [
            'status' => PublishableStatusCast::PENDING,
        ]);
    }
}

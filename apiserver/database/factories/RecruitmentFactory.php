<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\RecruitmentRoleCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\RecruitmentTypeCast;
use App\Models\Recruitment\Recruitment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recruitment\Recruitment>
 */
class RecruitmentFactory extends Factory
{
    protected $model = Recruitment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->jobTitle();

        return [
            'uuid' => Str::uuid()->toString(),
            'slug' => Str::slug($title).'-'.Str::random(6),
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'role' => fake()->randomElement(RecruitmentRoleCast::cases()),
            'location' => fake()->city().', India',
            'employment_type' => fake()->randomElement(RecruitmentTypeCast::cases()),
            'vacancy' => fake()->numberBetween(1, 10),
            'open_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'close_date' => now()->addDays(fake()->numberBetween(7, 60)),
            'is_payable' => false,
            'fees' => 0,
            'requirements' => [
                'Minimum 12th pass or equivalent',
                'Good communication skills',
                'Basic computer knowledge',
            ],
            'benefits' => [
                'Competitive salary',
                'Health insurance',
                'Professional development',
            ],
            'eligibility' => [
                'min_age' => 18,
                'max_age' => 45,
            ],
            'status' => RecruitmentStatusCast::Published,
        ];
    }

    /**
     * Draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RecruitmentStatusCast::Draft,
        ]);
    }

    /**
     * Published and open status.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RecruitmentStatusCast::Published,
            'open_date' => now()->subDay(),
            'close_date' => now()->addMonth(),
        ]);
    }

    /**
     * Closed status.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RecruitmentStatusCast::Closed,
        ]);
    }

    /**
     * Archived status.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RecruitmentStatusCast::Archived,
        ]);
    }

    /**
     * Payable recruitment with fees.
     */
    public function payable(int $feesInPaisa = 50000): static
    {
        return $this->state(fn (array $attributes) => [
            'is_payable' => true,
            'fees' => $feesInPaisa,
        ]);
    }

    /**
     * Free recruitment.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_payable' => false,
            'fees' => 0,
        ]);
    }

    /**
     * Expired recruitment (close date in past).
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RecruitmentStatusCast::Published,
            'open_date' => now()->subMonth(),
            'close_date' => now()->subDay(),
        ]);
    }

    /**
     * Future recruitment (open date in future).
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RecruitmentStatusCast::Published,
            'open_date' => now()->addWeek(),
            'close_date' => now()->addMonth(),
        ]);
    }

    /**
     * Specific role.
     */
    public function forRole(RecruitmentRoleCast $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Advisor role.
     */
    public function advisor(): static
    {
        return $this->forRole(RecruitmentRoleCast::Advisor);
    }

    /**
     * Trainer role.
     */
    public function trainer(): static
    {
        return $this->forRole(RecruitmentRoleCast::Trainer);
    }
}

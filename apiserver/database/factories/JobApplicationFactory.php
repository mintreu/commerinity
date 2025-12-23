<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\JobApplicationStatusCast;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recruitment\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => 'APP-'.now()->format('ym').'-'.strtoupper(Str::random(8)),
            'recruitment_id' => Recruitment::factory(),
            'applicant_type' => User::class,
            'applicant_id' => User::factory(),
            'guardian_name' => fake()->name(),
            'address_id' => null,
            'educations' => [
                [
                    'degree' => fake()->randomElement(['10th Pass', '12th Pass', 'Graduate', 'Post Graduate']),
                    'institution' => fake()->company().' School',
                    'year' => fake()->numberBetween(2015, 2024),
                ],
            ],
            'skills' => [
                [
                    'skill' => fake()->randomElement(['Communication', 'Sales', 'Leadership', 'Computer']),
                    'description' => fake()->sentence(),
                ],
            ],
            'experiences' => null,
            'reference_name' => fake()->name(),
            'reference_contact' => fake()->phoneNumber(),
            'is_paid' => false,
            'amount' => 0,
            'transaction_id' => null,
            'status' => JobApplicationStatusCast::Draft,
            'status_feedback' => null,
            'submitted_at' => null,
        ];
    }

    /**
     * Draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::Draft,
            'submitted_at' => null,
        ]);
    }

    /**
     * Awaiting payment status.
     */
    public function awaitingPayment(int $amount = 50000): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'amount' => $amount,
            'is_paid' => false,
            'submitted_at' => null,
        ]);
    }

    /**
     * Submitted status.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::Submitted,
            'is_paid' => true,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Under review status.
     */
    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::UnderReview,
            'is_paid' => true,
            'submitted_at' => now()->subDays(fake()->numberBetween(1, 7)),
        ]);
    }

    /**
     * Accepted status.
     */
    public function accepted(?string $feedback = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::Accepted,
            'is_paid' => true,
            'submitted_at' => now()->subDays(fake()->numberBetween(7, 30)),
            'status_feedback' => $feedback ?? 'Congratulations! You have been selected.',
        ]);
    }

    /**
     * Rejected status.
     */
    public function rejected(?string $feedback = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::Rejected,
            'is_paid' => true,
            'submitted_at' => now()->subDays(fake()->numberBetween(7, 30)),
            'status_feedback' => $feedback ?? 'Thank you for your interest. Unfortunately, we have selected other candidates.',
        ]);
    }

    /**
     * Withdrawn status.
     */
    public function withdrawn(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobApplicationStatusCast::Withdrawn,
            'is_paid' => true,
            'submitted_at' => now()->subDays(fake()->numberBetween(1, 14)),
            'status_feedback' => $reason ?? 'Application withdrawn by applicant.',
        ]);
    }

    /**
     * For a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'applicant_type' => User::class,
            'applicant_id' => $user->id,
        ]);
    }

    /**
     * For a specific recruitment.
     */
    public function forRecruitment(Recruitment $recruitment): static
    {
        return $this->state(fn (array $attributes) => [
            'recruitment_id' => $recruitment->id,
            'amount' => $recruitment->is_payable ? $recruitment->fees : 0,
        ]);
    }

    /**
     * With education details.
     */
    public function withEducations(array $educations): static
    {
        return $this->state(fn (array $attributes) => [
            'educations' => $educations,
        ]);
    }

    /**
     * With skills.
     */
    public function withSkills(array $skills): static
    {
        return $this->state(fn (array $attributes) => [
            'skills' => $skills,
        ]);
    }

    /**
     * With experiences.
     */
    public function withExperiences(array $experiences): static
    {
        return $this->state(fn (array $attributes) => [
            'experiences' => $experiences,
        ]);
    }
}

<?php

namespace Mintreu\LaravelRecruitment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mintreu\LaravelRecruitment\Models\JobApplication;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Mintreu\LaravelRecruitment\Models\JobApplication>
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
            //
        ];
    }
}

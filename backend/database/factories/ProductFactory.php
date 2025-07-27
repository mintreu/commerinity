<?php

namespace Database\Factories;

use App\Casts\ModelStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\FilterGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {


        return [
            'name' => $name = fake()->words(3, true),
            'parent_id' => null,
            'sku' => strtoupper(Str::random(10)),
            'url' => Str::slug($name) . '-' . Str::random(5),
            'type' => fake()->randomElement(collect(ProductTypeCast::cases())->pluck('value')->toArray()),
            'filter_group_id' => FilterGroup::inRandomOrder()->value('id'),
//            'tenant_id' => null,
//            'tenant_type' => null,
            'description' => fake()->paragraph(3),
            'short_description' => fake()->sentence(),
            'price' => fake()->numberBetween(100, 10000),
            'reward_point' => fake()->randomFloat(2, 0, 100),
            'is_returnable' => fake()->boolean(),
       //     'status' => fake()->randomElement(array_map(fn($case) => $case->value, ProductStatusCast::cases())),
           'view_count' => fake()->numberBetween(0, 1000),
            'meta_data' => [
                'meta_title' => fake()->sentence(6, true),
                'meta_description' => fake()->paragraph(2, true),
                'meta_keywords' => implode(', ', fake()->words(6)),
                'og_title' => fake()->sentence(6, true),
                'og_description' => fake()->paragraph(2, true),
                'twitter_title' => fake()->sentence(6, true),
                'twitter_description' => fake()->paragraph(2, true),
                'canonical_url' => url('/products/' . Str::slug($name)),
            ],

        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories\Ecommerce;

use App\Casts\ProductStatusCast;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->numberBetween(10000, 500000); // ₹100 - ₹5000 in paise

        return [
            'name' => ucfirst($name),
            'sku' => strtoupper(Str::random(8)),
            'url' => Str::slug($name).'-'.Str::random(4),
            'type' => 'simple',
            'filter_group_id' => FilterGroup::factory(),
            'status' => ProductStatusCast::PUBLISHED->value,
            'description' => fake()->paragraphs(2, true),
            'short_description' => fake()->sentence(),
            'price' => $price,
            'view_count' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Set product as published/active
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatusCast::PUBLISHED->value,
        ]);
    }

    /**
     * Set product as draft
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatusCast::DRAFT->value,
        ]);
    }

    /**
     * Set product as disabled
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatusCast::DISABLED->value,
        ]);
    }

    /**
     * Configure as a variant of another product
     */
    public function variantOf(Product $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'category_id' => $parent->category_id,
        ]);
    }

    /**
     * Set specific price
     */
    public function withPrice(int $priceInPaise): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $priceInPaise,
        ]);
    }
}

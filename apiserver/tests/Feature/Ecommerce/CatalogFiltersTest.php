<?php

declare(strict_types=1);

use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\FilterOption;
use App\Models\Ecommerce\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only relevant filter options for a category', function () {
    $apparelCategory = Category::create(['name' => 'Apparel', 'url' => 'apparel', 'status' => true]);
    $hairCategory = Category::create(['name' => 'Hair', 'url' => 'hair', 'status' => true]);

    $apparelGroup = FilterGroup::factory()->create(['name' => 'Apparel Filters']);
    $hairGroup = FilterGroup::factory()->create(['name' => 'Hair Filters']);

    $colorFilter = Filter::create(['name' => 'Color', 'is_required' => false]);
    $hairFilter = Filter::create(['name' => 'Hair Type', 'is_required' => false]);

    $apparelGroup->filters()->attach($colorFilter->id);
    $hairGroup->filters()->attach($hairFilter->id);

    $redOption = FilterOption::create(['filter_id' => $colorFilter->id, 'value' => 'Red']);
    $dryOption = FilterOption::create(['filter_id' => $hairFilter->id, 'value' => 'Dry']);

    $apparelProduct = Product::factory()->create([
        'category_id' => $apparelCategory->id,
        'filter_group_id' => $apparelGroup->id,
        'price' => 15000,
    ]);
    $apparelProduct->filterOptions()->attach($redOption->id, ['filter_id' => $colorFilter->id]);

    $hairProduct = Product::factory()->create([
        'category_id' => $hairCategory->id,
        'filter_group_id' => $hairGroup->id,
        'price' => 20000,
    ]);
    $hairProduct->filterOptions()->attach($dryOption->id, ['filter_id' => $hairFilter->id]);

    $response = $this->getJson('/api/catalog/filters?category=apparel');
    $response->assertSuccessful();

    $filterOptions = collect($response->json('data.filter_options'));
    expect($filterOptions->pluck('name'))->toContain('Color')
        ->and($filterOptions->pluck('name'))->not->toContain('Hair Type');
});

it('returns filter options across all products when category is not specified', function () {
    $apparelCategory = Category::create(['name' => 'Apparel', 'url' => 'apparel', 'status' => true]);
    $hairCategory = Category::create(['name' => 'Hair', 'url' => 'hair', 'status' => true]);

    $apparelGroup = FilterGroup::factory()->create(['name' => 'Apparel Filters']);
    $hairGroup = FilterGroup::factory()->create(['name' => 'Hair Filters']);

    $colorFilter = Filter::create(['name' => 'Color', 'is_required' => false]);
    $hairFilter = Filter::create(['name' => 'Hair Type', 'is_required' => false]);

    $apparelGroup->filters()->attach($colorFilter->id);
    $hairGroup->filters()->attach($hairFilter->id);

    $redOption = FilterOption::create(['filter_id' => $colorFilter->id, 'value' => 'Red']);
    $dryOption = FilterOption::create(['filter_id' => $hairFilter->id, 'value' => 'Dry']);

    $apparelProduct = Product::factory()->create([
        'category_id' => $apparelCategory->id,
        'filter_group_id' => $apparelGroup->id,
        'price' => 15000,
    ]);
    $apparelProduct->filterOptions()->attach($redOption->id, ['filter_id' => $colorFilter->id]);

    $hairProduct = Product::factory()->create([
        'category_id' => $hairCategory->id,
        'filter_group_id' => $hairGroup->id,
        'price' => 20000,
    ]);
    $hairProduct->filterOptions()->attach($dryOption->id, ['filter_id' => $hairFilter->id]);

    $response = $this->getJson('/api/catalog/filters');
    $response->assertSuccessful();

    $filterOptions = collect($response->json('data.filter_options'));
    expect($filterOptions->pluck('name'))->toContain('Color')
        ->and($filterOptions->pluck('name'))->toContain('Hair Type');
});

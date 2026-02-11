<?php

declare(strict_types=1);

use App\Casts\GstTaxCast;
use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Filament\Resources\Ecommerce\Products\Pages\CreateProduct;
use App\Filament\Resources\Ecommerce\Products\Pages\EditProduct;
use App\Models\Admin;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\FilterOption;
use App\Models\Ecommerce\Product;
use App\Services\Ecommerce\ProductManager;
use Livewire\Livewire;

function adminLogin(): Admin
{
    $admin = Admin::factory()->superAdmin()->create();

    test()->actingAs($admin, 'admin');

    return $admin;
}

function catalogueFixture(): array
{
    $category = Category::create([
        'name' => 'Category A',
        'url' => 'category-a',
        'status' => true,
    ]);

    $filterGroup = FilterGroup::create(['name' => 'Catalog Group']);

    $color = Filter::create(['name' => 'Color', 'is_required' => true]);
    $size = Filter::create(['name' => 'Size', 'is_required' => true]);
    $filterGroup->filters()->attach([$color->id, $size->id]);

    $red = FilterOption::create(['filter_id' => $color->id, 'value' => 'Red']);
    $blue = FilterOption::create(['filter_id' => $color->id, 'value' => 'Blue']);
    $small = FilterOption::create(['filter_id' => $size->id, 'value' => 'S']);
    $medium = FilterOption::create(['filter_id' => $size->id, 'value' => 'M']);

    return compact('category', 'filterGroup', 'color', 'size', 'red', 'blue', 'small', 'medium');
}

it('creates products from filament resource for each storefront type', function (string $type, bool $expectsVariants) {
    adminLogin();
    $fx = catalogueFixture();

    $sku = 'SKU-'.strtoupper($type).'-01';
    $url = 'sku-'.strtolower($type).'-01';

    $filterOptions = $type === ProductTypeCast::CONFIGURABLE->value
        ? [
            (string) $fx['color']->id => [(string) $fx['red']->id, (string) $fx['blue']->id],
            (string) $fx['size']->id => [(string) $fx['small']->id, (string) $fx['medium']->id],
        ]
        : [
            (string) $fx['color']->id => (string) $fx['red']->id,
            (string) $fx['size']->id => (string) $fx['small']->id,
        ];

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Product '.$type,
            'url' => $url,
            'sku' => $sku,
            'type' => $type,
            'status' => ProductStatusCast::PUBLISHED->value,
            'category_id' => $fx['category']->id,
            'filter_group_id' => $fx['filterGroup']->id,
            'price' => 120000,
            'gst_tax_type' => GstTaxCast::GST_5->value,
            'filter_options' => $filterOptions,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', $sku)->first();

    expect($product)->not()->toBeNull()
        ->and($product->type->value)->toBe($type);

    if ($expectsVariants) {
        expect($product->variants()->count())->toBe(4);
    } else {
        expect($product->variants()->count())->toBe(0);
    }
})->with([
    [ProductTypeCast::SIMPLE->value, false],
    [ProductTypeCast::BUNDLE->value, false],
    [ProductTypeCast::CONFIGURABLE->value, true],
]);

it('updates products from filament edit page for each storefront type', function (string $type, bool $expectsVariants) {
    adminLogin();
    $fx = catalogueFixture();

    $baseFilterOptions = $type === ProductTypeCast::CONFIGURABLE->value
        ? [
            (string) $fx['color']->id => [(string) $fx['red']->id, (string) $fx['blue']->id],
            (string) $fx['size']->id => [(string) $fx['small']->id],
        ]
        : [
            (string) $fx['color']->id => (string) $fx['red']->id,
            (string) $fx['size']->id => (string) $fx['small']->id,
        ];

    $product = ProductManager::create([
        'name' => 'Before '.$type,
        'url' => 'before-'.$type,
        'sku' => 'BEFORE-'.strtoupper($type),
        'type' => $type,
        'status' => ProductStatusCast::PUBLISHED->value,
        'category_id' => $fx['category']->id,
        'filter_group_id' => $fx['filterGroup']->id,
        'price' => 100000,
        'gst_tax_type' => GstTaxCast::GST_5->value,
        'filter_options' => $baseFilterOptions,
    ]);

    $updatedFilterOptions = $type === ProductTypeCast::CONFIGURABLE->value
        ? [
            (string) $fx['color']->id => [(string) $fx['blue']->id],
            (string) $fx['size']->id => [(string) $fx['medium']->id],
        ]
        : [
            (string) $fx['color']->id => (string) $fx['blue']->id,
            (string) $fx['size']->id => (string) $fx['medium']->id,
        ];

    Livewire::test(EditProduct::class, ['record' => $product->url])
        ->fillForm([
            'name' => 'After '.$type,
            'price' => 130000,
            'filter_options' => $updatedFilterOptions,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();

    expect($product->name)->toBe('After '.$type)
        ->and($product->price)->toBe(13000000);

    $selected = $product->filterOptions()->pluck('filter_options.id')->map(fn ($id) => (int) $id)->all();

    expect($selected)->toContain($fx['blue']->id)
        ->toContain($fx['medium']->id);

    if ($expectsVariants) {
        expect($product->variants()->count())->toBe(1);
    }
})->with([
    [ProductTypeCast::SIMPLE->value, false],
    [ProductTypeCast::BUNDLE->value, false],
    [ProductTypeCast::CONFIGURABLE->value, true],
]);

it('deletes products from filament edit page for each storefront type', function (string $type) {
    adminLogin();
    $fx = catalogueFixture();

    $product = ProductManager::create([
        'name' => 'Delete '.$type,
        'url' => 'delete-'.$type,
        'sku' => 'DELETE-'.strtoupper($type),
        'type' => $type,
        'status' => ProductStatusCast::PUBLISHED->value,
        'category_id' => $fx['category']->id,
        'filter_group_id' => $fx['filterGroup']->id,
        'price' => 100000,
        'gst_tax_type' => GstTaxCast::GST_5->value,
        'filter_options' => $type === ProductTypeCast::CONFIGURABLE->value
            ? [
                (string) $fx['color']->id => [(string) $fx['red']->id, (string) $fx['blue']->id],
                (string) $fx['size']->id => [(string) $fx['small']->id],
            ]
            : [
                (string) $fx['color']->id => (string) $fx['red']->id,
                (string) $fx['size']->id => (string) $fx['small']->id,
            ],
    ]);

    $variantCount = $product->variants()->count();

    Livewire::test(EditProduct::class, ['record' => $product->url])
        ->call('mountAction', 'delete')
        ->call('callMountedAction');

    expect(Product::whereKey($product->id)->exists())->toBeFalse();

    if ($type === ProductTypeCast::CONFIGURABLE->value) {
        expect($variantCount)->toBeGreaterThan(0)
            ->and(Product::where('parent_id', $product->id)->count())->toBe(0);
    }
})->with([
    [ProductTypeCast::SIMPLE->value],
    [ProductTypeCast::BUNDLE->value],
    [ProductTypeCast::CONFIGURABLE->value],
]);

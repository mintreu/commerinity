<?php

declare(strict_types=1);

use App\Casts\UserTypeCast;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('applies stage-targeted sale for authenticated users and global sale for guests', function () {
    $product = Product::factory()->create(['price' => 10000]);
    ProductStock::factory()->for($product)->create([
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    $globalSale = Sale::factory()->create();
    SaleProduct::factory()->for($globalSale)->for($product)->create([
        'sale_price' => 9000,
        'sort_order' => 2,
    ]);

    $stage = Stage::factory()->create();
    $level = Level::factory()->forStage($stage)->create();

    $user = User::factory()->create();
    UserSubscription::factory()->forUser($user)->active()->create([
        'stage_id' => $stage->id,
        'level_id' => $level->id,
        'current_level_id' => $level->id,
    ]);

    $stageSale = Sale::factory()->create();
    SaleProduct::factory()->for($stageSale)->for($product)->create([
        'sale_price' => 8000,
        'sort_order' => 1,
        'target_type' => Stage::class,
        'target_id' => $stage->id,
    ]);

    $guestResponse = $this->getJson('/api/catalog/products');
    $guestResponse->assertSuccessful();
    expect($guestResponse->json('data.0.price'))->toBe(9000);

    $memberResponse = $this->actingAs($user, 'sanctum')->getJson('/api/catalog/products');
    $memberResponse->assertSuccessful();
    expect($memberResponse->json('data.0.price'))->toBe(8000);
});

it('applies user-type sale only for matching users in product detail', function () {
    $product = Product::factory()->create(['price' => 12000]);
    ProductStock::factory()->for($product)->create([
        'init_quantity' => 10,
        'sold_quantity' => 0,
    ]);

    $sale = Sale::factory()->create([
        'target_user_types' => [UserTypeCast::MEMBER->value],
    ]);
    SaleProduct::factory()->for($sale)->for($product)->create([
        'sale_price' => 7000,
    ]);

    $guestResponse = $this->getJson("/api/catalog/products/{$product->url}");
    $guestResponse->assertSuccessful();
    expect($guestResponse->json('data.price'))->toBe(12000);

    $member = User::factory()->withType(UserTypeCast::MEMBER->value)->create();
    $memberResponse = $this->actingAs($member, 'sanctum')->getJson("/api/catalog/products/{$product->url}");
    $memberResponse->assertSuccessful();
    expect($memberResponse->json('data.price'))->toBe(7000);
});

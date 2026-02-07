<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\SaleActionTypeCast;
use App\Casts\UserTypeCast;
use App\Casts\VoucherActionTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\Voucher;
use App\Services\Ecommerce\SaleManager;
use App\Services\Ecommerce\VoucherManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::first();
        $product = Product::whereNull('parent_id')->first();

        if (! $category || ! $product) {
            $this->command->warn('PromotionSeeder skipped: requires at least 1 category and 1 product.');
            return;
        }

        $this->seedSales($category, $product);
        $this->seedVouchers($category, $product);
    }

    private function seedSales(Category $category, Product $product): void
    {
        $siteSale = Sale::firstOrCreate(
            ['name' => 'Sitewide Demo Sale'],
            [
                'uuid' => (string) Str::uuid(),
                'description' => 'Applies to all products',
                'starts_from' => now()->subDay(),
                'ends_till' => now()->addDays(10),
                'status' => true,
                'condition_type' => 'match_any',
                'conditions' => [],
                'end_other_rules' => false,
                'action_type' => SaleActionTypeCast::BY_PERCENT,
                'discount_amount' => 10,
                'sort_order' => 1,
            ]
        );

        $categorySale = Sale::firstOrCreate(
            ['name' => 'Category Demo Sale'],
            [
                'uuid' => (string) Str::uuid(),
                'description' => 'Applies to a single category',
                'starts_from' => now()->subDay(),
                'ends_till' => now()->addDays(7),
                'status' => true,
                'condition_type' => 'match_all',
                'conditions' => [
                    ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [$category->id]],
                ],
                'end_other_rules' => false,
                'action_type' => SaleActionTypeCast::BY_FIXED,
                'discount_amount' => 500,
                'sort_order' => 2,
            ]
        );
        $categorySale->categories()->sync([$category->id]);

        $memberSale = Sale::firstOrCreate(
            ['name' => 'Member Only Demo Sale'],
            [
                'uuid' => (string) Str::uuid(),
                'description' => 'Applies to members/promoters only',
                'starts_from' => now()->subDay(),
                'ends_till' => now()->addDays(5),
                'status' => true,
                'condition_type' => 'match_any',
                'conditions' => [],
                'end_other_rules' => false,
                'action_type' => SaleActionTypeCast::BY_PERCENT,
                'discount_amount' => 5,
                'sort_order' => 3,
                'target_user_types' => [
                    UserTypeCast::MEMBER->value,
                    UserTypeCast::PROMOTER->value,
                ],
            ]
        );
        $memberSale->products()->sync([$product->id]);

        SaleManager::make()->reindexSaleableProducts();
    }

    private function seedVouchers(Category $category, Product $product): void
    {
        $cartVoucher = Voucher::firstOrCreate(
            ['name' => 'Cart Demo Voucher'],
            [
                'description' => 'Cart level percent discount',
                'starts_from' => now()->subDay()->toDateString(),
                'ends_till' => now()->addDays(7)->toDateString(),
                'status' => true,
                'usage_per_customer' => 1,
                'coupon_usage_limit' => 10,
                'times_used' => 0,
                'condition_type' => 'match_all',
                'conditions' => [
                    ['attribute' => 'cart|subTotal', 'operator' => '>=', 'value' => 10000],
                ],
                'end_other_rules' => false,
                'action_type' => VoucherActionTypeCast::BY_PERCENT,
                'discount_amount' => 10,
                'discount_quantity' => 1,
                'discount_step' => null,
                'apply_to_shipping' => false,
                'free_shipping' => false,
                'min_cart_value' => 0,
                'min_quantity' => 0,
                'sort_order' => 1,
            ]
        );
        if ($cartVoucher->codes()->count() === 0) {
            VoucherManager::generateCouponCodes($cartVoucher);
        }

        $productVoucher = Voucher::firstOrCreate(
            ['name' => 'Product Demo Voucher'],
            [
                'description' => 'Item level fixed discount',
                'starts_from' => now()->subDay()->toDateString(),
                'ends_till' => now()->addDays(7)->toDateString(),
                'status' => true,
                'usage_per_customer' => 2,
                'coupon_usage_limit' => 20,
                'times_used' => 0,
                'condition_type' => 'match_any',
                'conditions' => [
                    ['attribute' => 'product|category_id', 'operator' => '==', 'value' => [$category->id]],
                ],
                'end_other_rules' => false,
                'action_type' => VoucherActionTypeCast::BY_FIXED,
                'discount_amount' => 200,
                'discount_quantity' => 1,
                'discount_step' => null,
                'apply_to_shipping' => false,
                'free_shipping' => false,
                'min_cart_value' => 0,
                'min_quantity' => 0,
                'sort_order' => 2,
            ]
        );
        $productVoucher->products()->sync([$product->id]);
        if ($productVoucher->codes()->count() === 0) {
            VoucherManager::generateCouponCodes($productVoucher);
        }
    }
}

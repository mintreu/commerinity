<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\SaleProduct;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;

final class SaleResolver
{
    public function resolveSale(Product $product, ?User $user = null): ?SaleProduct
    {
        $productIds = [$product->id];
        $activeSales = $this->getActiveSalesForProducts($productIds, $user);

        $saleInfo = $activeSales[$product->id] ?? null;

        return $saleInfo['sale_product'] ?? null;
    }

    public function resolveSalePrice(SaleProduct $saleProduct, int $originalPrice): int
    {
        if ($saleProduct->sale_price > 0) {
            return $saleProduct->sale_price;
        }

        return $saleProduct->getFinalPrice($originalPrice);
    }

    private function resolveSaleContext(?User $user): array
    {
        if (! $user instanceof User) {
            return [
                'user_id' => null,
                'user_type' => null,
                'stage_id' => null,
                'level_id' => null,
            ];
        }

        $subscription = UserSubscription::getActiveForUser($user->id);

        return [
            'user_id' => $user->id,
            'user_type' => $user->type?->value,
            'stage_id' => $subscription?->stage_id,
            'level_id' => $subscription?->current_level_id
                ?? $subscription?->level_id
                ?? $user->level_id,
        ];
    }

    private function getActiveSalesForProducts(array $productIds, ?User $user = null): array
    {
        if (empty($productIds)) {
            return [];
        }

        $context = $this->resolveSaleContext($user);
        $userType = $context['user_type'];
        $userStageId = $context['stage_id'];
        $userLevelId = $context['level_id'];

        $saleProductQuery = SaleProduct::query()
            ->active()
            ->whereIn('product_id', $productIds)
            ->ordered()
            ->where(function ($q) use ($context, $userStageId, $userLevelId): void {
                $q->whereNull('target_type')
                    ->orWhereIn('target_type', [Category::class, Product::class]);

                if ($context['user_id']) {
                    $q->orWhere(function ($targetQuery) use ($context): void {
                        $targetQuery->where('target_type', User::class)
                            ->where('target_id', $context['user_id']);
                    });

                    if ($userStageId) {
                        $q->orWhere(function ($targetQuery) use ($userStageId): void {
                            $targetQuery->where('target_type', Stage::class)
                                ->where('target_id', $userStageId);
                        });
                    }

                    if ($userLevelId) {
                        $q->orWhere(function ($targetQuery) use ($userLevelId): void {
                            $targetQuery->where('target_type', Level::class)
                                ->where('target_id', $userLevelId);
                        });
                    }
                }
            })
            ->where(function ($q) use ($userType): void {
                $q->whereNull('sale_id')
                    ->orWhereHas('sale', function ($saleQuery) use ($userType): void {
                        $saleQuery->where(function ($userTypeQuery) use ($userType): void {
                            $userTypeQuery->whereNull('target_user_types')
                                ->orWhereJsonLength('target_user_types', 0);

                            if ($userType) {
                                $userTypeQuery->orWhereJsonContains('target_user_types', $userType);
                            }
                        });
                    });
            })
            ->with('sale');

        $activeSales = [];

        foreach ($saleProductQuery->get() as $saleProduct) {
            if (! isset($activeSales[$saleProduct->product_id]) && $saleProduct->isActive()) {
                $activeSales[$saleProduct->product_id] = [
                    'sale_product' => $saleProduct,
                    'sale' => $saleProduct->sale,
                    'ends_at' => $saleProduct->ends_till ?? $saleProduct->sale?->ends_till,
                ];
            }
        }

        return $activeSales;
    }
}

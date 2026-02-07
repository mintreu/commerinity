<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\ProductResource;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\SaleProduct;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DealsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->applyStockContext($request);
        $perPage = max(1, (int) $request->input('per_page', 20));
        $page = max(1, (int) $request->input('page', 1));

        $categoryIds = null;
        if ($request->filled('category')) {
            $category = Category::where('url', $request->input('category'))->first();

            if ($category) {
                $categoryIds = $category->descendantsAndSelf()->pluck('id')->all();
            }
        }

        $user = auth('sanctum')->user();
        $saleProductQuery = $this->prepareActiveSaleProductQuery($user);

        if ($categoryIds) {
            $saleProductQuery->whereHas('product', fn ($query) => $query
                ->whereIn('category_id', $categoryIds)
                ->orWhereHas('categories', fn ($sub) => $sub->whereIn('categories.id', $categoryIds))
            );
        }

        $totalDeals = (clone $saleProductQuery)
            ->select('product_id')
            ->distinct()
            ->count('product_id');

        $saleProducts = (clone $saleProductQuery)
            ->with('product')
            ->paginate($perPage, ['*'], 'page', $page);

        $uniqueSaleProducts = $saleProducts->getCollection()
            ->filter(fn ($saleProduct) => $saleProduct->product)
            ->unique('product_id')
            ->values();

        $productIds = $uniqueSaleProducts->pluck('product_id')->values()->all();

        if (empty($productIds)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_deals' => 0,
                        'avg_discount' => 0,
                        'ends_at' => null,
                    ],
                    'items' => [],
                    'pagination' => [
                        'current_page' => $saleProducts->currentPage(),
                        'last_page' => max(1, (int) ceil(max(1, $totalDeals) / $perPage)),
                        'per_page' => $perPage,
                        'total' => $totalDeals,
                        'has_more' => false,
                    ],
                ],
            ]);
        }

        $products = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->whereIn('id', $productIds)
            ->with(['category', 'media'])
            ->withStockInfo()
            ->get()
            ->keyBy('id');

        $orderedProducts = collect($productIds)
            ->map(fn ($id) => $products->get($id))
            ->filter()
            ->values();

        $collection = new EloquentCollection($orderedProducts->all());
        $collection->load(['availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at')]);

        $activeSales = $this->getActiveSalesForProducts($collection->pluck('id')->all(), $user);

        foreach ($collection as $product) {
            $product->setRelation('activeSaleInfo', $activeSales[$product->id] ?? null);
        }

        $stats = $this->calculateDealStats($collection, $activeSales, $totalDeals);
        $lastPage = max(1, (int) ceil($totalDeals / $perPage));
        $currentPage = min($saleProducts->currentPage(), $lastPage);

        $pagination = [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $totalDeals,
            'has_more' => $currentPage < $lastPage,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'items' => ProductResource::collection($collection)->response()->getData(true)['data'],
                'pagination' => $pagination,
            ],
        ]);
    }

    protected function prepareActiveSaleProductQuery(?User $user): Builder
    {
        $context = $this->resolveSaleContext($user);
        $userType = $context['user_type'];
        $userStageId = $context['stage_id'];
        $userLevelId = $context['level_id'];

        return SaleProduct::query()
            ->active()
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
    }

    protected function getActiveSalesForProducts(array $productIds, ?User $user = null): array
    {
        if (empty($productIds)) {
            return [];
        }

        $saleProductQuery = $this->prepareActiveSaleProductQuery($user)
            ->whereIn('product_id', $productIds);

        $activeSales = [];

        foreach ($saleProductQuery->get() as $saleProduct) {
            if (! isset($activeSales[$saleProduct->product_id]) && $saleProduct->isActive()) {
                $activeSales[$saleProduct->product_id] = [
                    'type' => 'product_sale',
                    'sale_product' => $saleProduct,
                    'name' => $saleProduct->sale?->name ?? 'Special Offer',
                    'ends_at' => $saleProduct->ends_till ?? $saleProduct->sale?->ends_till,
                ];
            }
        }

        return $activeSales;
    }

    protected function calculateDealStats(EloquentCollection $products, array $activeSales, int $totalDeals): array
    {
        $discounts = [];
        $endsAt = null;

        foreach ($products as $product) {
            $saleInfo = $activeSales[$product->id] ?? null;
            if (! $saleInfo) {
                continue;
            }

            $saleProduct = $saleInfo['sale_product'] ?? null;
            if (! $saleProduct) {
                continue;
            }

            $originalPrice = $product->getPrice();
            if ($originalPrice <= 0) {
                continue;
            }

            $salePrice = $saleProduct->getFinalPrice($originalPrice);
            if ($salePrice >= $originalPrice) {
                continue;
            }

            $discounts[] = (int) round((($originalPrice - $salePrice) / $originalPrice) * 100);

            $endsValue = $saleInfo['ends_at'];
            if ($endsValue instanceof Carbon) {
                $saleEnds = $endsValue;
            } elseif (is_string($endsValue)) {
                $saleEnds = Carbon::parse($endsValue);
            } else {
                $saleEnds = null;
            }

            if (isset($saleEnds) && (! $endsAt || $saleEnds->lt($endsAt))) {
                $endsAt = $saleEnds;
            }
        }

        return [
            'total_deals' => $totalDeals,
            'avg_discount' => $discounts ? (int) round(array_sum($discounts) / count($discounts)) : 0,
            'ends_at' => $endsAt?->toIso8601String(),
        ];
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

    private function applyStockContext(Request $request): void
    {
        if (! $request->filled('shipping_address_id')) {
            return;
        }

        $user = auth('sanctum')->user();
        if (! $user instanceof User) {
            return;
        }

        $address = $user->addresses()
            ->where('uuid', $request->input('shipping_address_id'))
            ->first();

        if (! $address) {
            return;
        }

        $request->attributes->set('stock_context', $address);
    }
}

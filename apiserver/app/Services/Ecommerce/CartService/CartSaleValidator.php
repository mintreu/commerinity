<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService;

use App\Casts\ConditionMatchingCast;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\SaleProduct;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Collection;

/**
 * Validates sale rules for a single cart line.
 */
final class CartSaleValidator
{
    protected CartService $cartService;
    protected Cart $cartLine;
    protected Product $product;
    protected Collection $sales;
    protected ?SaleProduct $applicableSale = null;
    protected int $resolvedPrice = 0;

    public function __construct(CartService $cartService, Cart $cartLine)
    {
        $this->cartService = $cartService;
        $this->cartLine = $cartLine;
        $this->product = $cartLine->cartable;
        $this->resolvedPrice = $this->product->getPrice();
        $this->sales = $this->loadSales();
    }

    public static function make(CartService $cartService, Cart $cartLine): self
    {
        return new self($cartService, $cartLine);
    }

    public function setResolvedPrice(int $price): self
    {
        $this->resolvedPrice = $price;

        return $this;
    }

    public function validate(): bool
    {
        return $this->validateSale();
    }

    public function toArray(): array
    {
        $salePrice = $this->applicableSale ? $this->resolveSalePrice($this->applicableSale) : null;

        return [
            'product_id' => $this->product->id,
            'sales' => [
                'applicable' => [
                    'name' => $this->applicableSale?->sale?->name ?? null,
                    'sale_price' => $salePrice,
                    'sale_price_formatted' => $salePrice ? MoneyService::format($salePrice) : null,
                    'discount' => $salePrice ? $this->product->getPrice() - $salePrice : null,
                    'discount_formatted' => $salePrice ? MoneyService::format($this->product->getPrice() - $salePrice) : null,
                    'sale_product' => $this->applicableSale,
                ],
                'available' => $this->sales,
            ],
        ];
    }

    private function validateSale(): bool
    {
        if ($this->sales->isEmpty()) {
            return false;
        }

        $endRuleSale = $this->sales->firstWhere('end_other_rules', true);
        if ($endRuleSale && $this->isSaleApplicable($endRuleSale)) {
            $this->applicableSale = $endRuleSale;
            $this->resolvedPrice = $this->resolveSalePrice($endRuleSale);

            return true;
        }

        foreach ($this->sales->sortBy('sort_order') as $sale) {
            if ($this->isSaleApplicable($sale)) {
                $this->applicableSale = $sale;
                $this->resolvedPrice = $this->resolveSalePrice($sale);

                return true;
            }
        }

        return false;
    }

    private function isSaleApplicable(SaleProduct $saleProduct): bool
    {
        $sale = $saleProduct->sale;
        $conditions = $sale?->conditions ?? [];
        $matching = $sale?->condition_type ?? ConditionMatchingCast::MATCH_ALL;

        if (empty($conditions)) {
            return true;
        }

        $results = [];
        foreach ($conditions as $condition) {
            $results[] = $this->validateSaleCondition($condition);
        }

        return $matching->evaluate($results);
    }

    private function validateSaleCondition(array $condition): bool
    {
        $attribute = $condition['attribute'] ?? '';
        $chunks = explode('|', $attribute);
        $field = $chunks[1] ?? null;

        if ($field && isset($this->product->{$field})) {
            $cartValue = $this->product->{$field};
            if ($field === 'price') {
                $cartValue = $this->resolvedPrice;
            }

            $operator = $condition['operator'] ?? '==';
            $expected = $condition['value'];

            return $this->validateCompare($cartValue, $operator, $expected);
        }

        $this->cartService->setError("Invalid sale condition attribute: {$attribute}");

        return false;
    }

    private function validateCompare(int|string $cartValue, string $operator, mixed $expected): bool
    {
        return match ($operator) {
            '>' => $cartValue > $expected,
            '<' => $cartValue < $expected,
            '>=' => $cartValue >= $expected,
            '<=' => $cartValue <= $expected,
            '==' => $cartValue == $expected,
            '=' => $cartValue == $expected,
            '!=' => $cartValue != $expected,
            '<>' => $cartValue != $expected,
            default => false,
        };
    }

    public function getResolvedPrice(): int
    {
        return $this->resolvedPrice;
    }

    public function getApplicableSale(): ?SaleProduct
    {
        return $this->applicableSale;
    }

    private function loadSales(): Collection
    {
        return SaleProduct::query()
            ->with('sale')
            ->active()
            ->where('product_id', $this->product->id)
            ->ordered()
            ->get();
    }

    private function resolveSalePrice(SaleProduct $saleProduct): int
    {
        if ($saleProduct->sale_price > 0) {
            return $saleProduct->sale_price;
        }

        return $saleProduct->getFinalPrice($this->product->getPrice());
    }
}

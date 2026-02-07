<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\VoucherActionTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Voucher;
use App\Models\Ecommerce\VoucherCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Voucher Manager Service
 *
 * Handles voucher creation, validation, and coupon code generation.
 * Provides reusable methods for creating vouchers with conditions.
 */
final class VoucherManager
{
    protected array $category;
    protected array $filterGroup;
    protected Collection $filters;

    public function __construct()
    {
        $this->category = Category::with('children', 'parent')
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();

        $this->filterGroup = FilterGroup::all()->pluck('name', 'id')->toArray();

        $this->filters = Filter::with('options')
            ->has('options')
            ->get();
    }

    public static function make(): static
    {
        return new self;
    }

    /**
     * Get available condition options for Filament form schema
     */
    public function getCondition(): Collection
    {
        $collection = collect([
            [
                'key' => 'cart',
                'label' => trans('Cart Attributes'),
                'children' => $this->getCartRelatedChildren(),
            ],
            [
                'key' => 'cart_item',
                'label' => trans('Cart Item Attributes'),
                'children' => $this->getCartItemRelatedChildren(),
            ],
            [
                'key' => 'product',
                'label' => trans('Product Attributes'),
                'children' => $this->getProductRelatedChildren(),
            ],
        ]);

        $conditions = collect();
        $conditions = $collection->map(function ($item) use ($conditions) {
            return $conditions->merge($item['children']);
        });

        return collect(array_merge(
            $conditions[0]->toArray(),
            $conditions[1]->toArray(),
            $conditions[2]->toArray()
        ));
    }

    protected function getOperator(string $operatorType): array
    {
        return match ($operatorType) {
            'text' => [
                '{}' => 'Contain',
                '!{}' => 'Not Contain',
            ],
            'number', 'price', 'integer', 'decimal' => [
                '==' => 'Equal With',
                '!=' => 'Not Equal',
                '>' => 'Greater than',
                '<' => 'Less than',
                '>=' => 'Greater than or Equal',
                '<=' => 'Less than or Equal',
            ],
            'select', 'multiselect' => [
                '==' => 'Equal With',
                '!=' => 'Not Equal',
            ],
            default => [],
        };
    }

    private function getCartRelatedChildren(): array
    {
        return [
            [
                'key' => 'cart|subTotal',
                'type' => 'price',
                'operator' => $this->getOperator('price'),
                'label' => trans('Cart Subtotal'),
            ],
            [
                'key' => 'cart|totalQuantity',
                'type' => 'integer',
                'operator' => $this->getOperator('integer'),
                'label' => trans('Cart Total Item Qty'),
            ],
        ];
    }

    private function getCartItemRelatedChildren(): array
    {
        return [
            [
                'key' => 'cart_item|quantity',
                'type' => 'integer',
                'operator' => $this->getOperator('integer'),
                'label' => trans('Cart Item Qty'),
            ],
        ];
    }

    private function getProductRelatedChildren(): array
    {
        $productArray = [
            [
                'key' => 'product|category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Product Categories'),
                'options' => $this->category,
            ],
            [
                'key' => 'product|children::category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Product Categories (Children)'),
                'options' => Category::whereNotNull('parent_id')->pluck('name', 'id')->toArray(),
            ],
            [
                'key' => 'product|parent::category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Product Categories (Parent)'),
                'options' => Category::whereNull('parent_id')->pluck('name', 'id')->toArray(),
            ],
        ];

        return array_merge($productArray, $this->getAttributeList());
    }

    private function getAttributeList(): array
    {
        $attrBag = [];
        $allAttribute = $this->filters;

        foreach ($allAttribute as $attr) {
            $key = 'product|'.$attr->name;
            $attrBag[] = [
                'key' => Str::lower($key),
                'type' => $attr->type,
                'operator' => $this->getOperator(Str::lower($attr->type)),
                'label' => trans(Str::ucfirst($attr->name)),
                'options' => $attr->options->pluck('value', 'id')->toArray(),
            ];
        }

        return $attrBag;
    }

    /**
     * Create a new voucher with optional coupon codes
     */
    public static function create(array $data, bool $generateCodes = true): Voucher
    {
        $voucher = Voucher::create($data);

        if ($generateCodes) {
            self::generateCouponCodes($voucher);
        }

        return $voucher;
    }

    /**
     * Create a percentage-based voucher
     */
    public static function createPercentOff(
        string $name,
        float $percentOff,
        ?int $minCartValue = null,
        ?int $maxDiscount = null,
        int $usageLimit = 0,
        int $usagePerCustomer = 1
    ): Voucher {
        $data = [
            'name' => $name,
            'action_type' => VoucherActionTypeCast::BY_PERCENT,
            'discount_amount' => (int) $percentOff,
            'min_cart_value' => $minCartValue ?? 0,
            'coupon_usage_limit' => $usageLimit,
            'usage_per_customer' => $usagePerCustomer,
            'status' => true,
            'starts_from' => now(),
            'ends_till' => now()->addYear(),
            'condition_type' => 'match_any',
        ];

        return self::create($data);
    }

    /**
     * Create a fixed amount voucher
     */
    public static function createFixedOff(
        string $name,
        int $fixedAmount,
        ?int $minCartValue = null,
        int $usageLimit = 0,
        int $usagePerCustomer = 1
    ): Voucher {
        $data = [
            'name' => $name,
            'action_type' => VoucherActionTypeCast::BY_FIXED,
            'discount_amount' => $fixedAmount,
            'min_cart_value' => $minCartValue ?? 0,
            'coupon_usage_limit' => $usageLimit,
            'usage_per_customer' => $usagePerCustomer,
            'status' => true,
            'starts_from' => now(),
            'ends_till' => now()->addYear(),
            'condition_type' => 'match_any',
        ];

        return self::create($data);
    }

    /**
     * Create a free shipping voucher
     */
    public static function createFreeShipping(
        string $name,
        ?int $minCartValue = null,
        int $usageLimit = 0,
        int $usagePerCustomer = 1
    ): Voucher {
        $data = [
            'name' => $name,
            'action_type' => VoucherActionTypeCast::TO_FIXED,
            'discount_amount' => 0,
            'free_shipping' => true,
            'min_cart_value' => $minCartValue ?? 0,
            'coupon_usage_limit' => $usageLimit,
            'usage_per_customer' => $usagePerCustomer,
            'status' => true,
            'starts_from' => now(),
            'ends_till' => now()->addYear(),
            'condition_type' => 'match_any',
        ];

        return self::create($data);
    }

    /**
     * Generate coupon codes for a voucher
     */
    public static function generateCouponCodes(Voucher $voucher, int $count = 5): void
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = [
                'voucher_id' => $voucher->id,
                'code' => self::generateUniqueCode(),
                'is_primary' => $i === 0,
                'starts_from' => $voucher->starts_from,
                'ends_till' => $voucher->ends_till,
                'coupon_usage_limit' => $voucher->coupon_usage_limit,
                'usage_per_user' => $voucher->usage_per_customer,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        VoucherCode::insert($codes);
    }

    /**
     * Generate a unique coupon code
     */
    private static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (VoucherCode::where('code', $code)->exists());

        return $code;
    }

    /**
     * Create a voucher with BOGO (Buy One Get One) logic
     */
    public static function createBogo(
        string $name,
        int $discountPercent,
        ?int $minQuantity = 2,
        int $usageLimit = 0
    ): Voucher {
        $data = [
            'name' => $name,
            'action_type' => VoucherActionTypeCast::BY_PERCENT,
            'discount_amount' => $discountPercent,
            'min_quantity' => $minQuantity,
            'discount_quantity' => 1,
            'discount_step' => $minQuantity,
            'coupon_usage_limit' => $usageLimit,
            'usage_per_customer' => 1,
            'status' => true,
            'starts_from' => now(),
            'ends_till' => now()->addYear(),
            'condition_type' => 'match_all',
        ];

        return self::create($data);
    }

    /**
     * Validate a coupon code
     */
    public static function validateCode(string $code, int $cartTotal, int $cartQuantity): array
    {
        $voucherCode = VoucherCode::where('code', strtoupper($code))->first();

        if (! $voucherCode) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code',
            ];
        }

        $voucher = $voucherCode->voucher;

        // Check if voucher is active
        if (! $voucher->isActive()) {
            return [
                'valid' => false,
                'message' => 'This coupon has expired',
            ];
        }

        // Check if code is still valid
        if (! $voucherCode->isValid()) {
            return [
                'valid' => false,
                'message' => 'This coupon has reached its usage limit',
            ];
        }

        // Check minimum requirements
        if (! $voucher->meetsMinimumRequirements($cartTotal, $cartQuantity)) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Minimum cart value of %s required',
                    number_to_currency($voucher->min_cart_value / 100, 'INR')
                ),
            ];
        }

        return [
            'valid' => true,
            'voucher' => $voucher,
            'voucher_code' => $voucherCode,
            'discount' => $voucher->calculateDiscount($cartTotal),
        ];
    }

    /**
     * Redeem a coupon code
     */
    public static function redeemCode(string $code, int $userId): array
    {
        $validation = self::validateCode($code, 0, 0);

        if (! $validation['valid']) {
            return $validation;
        }

        $voucherCode = $validation['voucher_code'];
        $voucher = $validation['voucher'];

        // Check user usage limit
        $userUsageCount = $voucherCode->redemptions()
            ->where('user_id', $userId)
            ->count();

        if ($userUsageCount >= $voucher->usage_per_customer) {
            return [
                'valid' => false,
                'message' => 'You have already used this coupon the maximum number of times',
            ];
        }

        // Record redemption
        $voucherCode->redemptions()->create([
            'user_id' => $userId,
            'redeemed_at' => now(),
        ]);

        // Increment voucher usage
        $voucher->increment('times_used');

        return [
            'valid' => true,
            'voucher' => $voucher,
            'voucher_code' => $voucherCode,
        ];
    }
}

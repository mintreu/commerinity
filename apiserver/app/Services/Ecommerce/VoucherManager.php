<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\VoucherActionTypeCast;
use App\Models\Ecommerce\Voucher;
use App\Models\Ecommerce\VoucherCode;
use Illuminate\Support\Str;

/**
 * Voucher Manager Service
 *
 * Handles voucher creation, validation, and coupon code generation.
 * Provides reusable methods for creating vouchers with conditions.
 */
final class VoucherManager
{
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
                'code' => self::generateUniqueCode(),
                'is_primary' => $i === 0,
                'starts_from' => $voucher->starts_from,
                'ends_till' => $voucher->ends_till,
                'coupon_usage_limit' => $voucher->coupon_usage_limit,
                'usage_per_customer' => $voucher->usage_per_customer,
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

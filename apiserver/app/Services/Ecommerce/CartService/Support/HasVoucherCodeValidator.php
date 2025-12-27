<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService\Support;

use App\Models\Ecommerce\VoucherCode;
use Illuminate\Database\Eloquent\Model;

trait HasVoucherCodeValidator
{
    /**
     * Set and validate coupon code
     */
    public function setCouponCode(string|VoucherCode $voucherCode): void
    {
        if (is_string($voucherCode)) {
            $voucherCode = VoucherCode::where('code', $voucherCode)->first();
            if (! $voucherCode) {
                $this->setError('Invalid coupon code.');
                $this->validCoupon = false;
                $this->couponCode = null;
                $this->forgetCouponInSession();

                return;
            }
        }

        if ($this->validateCouponCode($voucherCode, $this->customer ?? null)) {
            $this->validCoupon = true;
            $this->couponCode = $voucherCode->code;
            $this->putCouponInSession($this->couponCode);

            return;
        }

        $this->validCoupon = false;
        $this->couponCode = null;
        $this->forgetCouponInSession();
    }

    /**
     * Validate a coupon code
     */
    protected function validateCouponCode(VoucherCode|string|null $voucher, ?Model $user = null): bool
    {
        if (! ($voucher instanceof VoucherCode)) {
            $this->setError('Coupon code not found.');

            return false;
        }

        if (method_exists($voucher, 'isActive') && ! $voucher->isActive()) {
            $this->setError('Coupon code is not active.');

            return false;
        }

        if (method_exists($voucher, 'isExpired') && $voucher->isExpired()) {
            $this->setError('Coupon code has expired.');

            return false;
        }

        // Check global usage limit
        $globalLimit = $voucher->getEffectiveUsageLimit();
        if ($globalLimit > 0 && $voucher->times_used >= $globalLimit) {
            $this->setError('Coupon usage limit reached.');

            return false;
        }

        // Check per-user usage limit
        if ($user) {
            $perUserLimit = $voucher->getEffectiveUsagePerUser();
            if ($perUserLimit > 0) {
                $timesByUser = (int) $voucher->usageByUser($user);
                if ($timesByUser >= $perUserLimit) {
                    $this->setError('You have already used this coupon the maximum number of times.');

                    return false;
                }
            }
        }

        return $this->getErrors() === null;
    }

    /**
     * Get session key for coupon storage
     */
    protected function sessionCouponKey(): string
    {
        return config('cart.coupon.session_key', 'cart.coupon');
    }

    /**
     * Store coupon in session
     */
    protected function putCouponInSession(string $code): void
    {
        session()->put($this->sessionCouponKey(), $code);
        session()->save();
    }

    /**
     * Get coupon from session
     */
    protected function getCouponFromSession(): ?string
    {
        $code = session()->get($this->sessionCouponKey());

        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * Remove coupon from session
     */
    protected function forgetCouponInSession(): void
    {
        session()->forget($this->sessionCouponKey());
        session()->save();
    }

    /**
     * Load coupon from session if exists
     */
    public function loadCouponFromSession(): void
    {
        $code = $this->getCouponFromSession();
        if ($code) {
            $this->couponCode = $code;
            $this->validCoupon = true;
        }
    }

    /**
     * Clear applied coupon
     */
    public function clearCoupon(): void
    {
        $this->couponCode = null;
        $this->validCoupon = false;
        $this->forgetCouponInSession();
    }

    /**
     * Check if coupon is valid
     */
    public function hasCoupon(): bool
    {
        return $this->validCoupon && ! empty($this->couponCode);
    }
}

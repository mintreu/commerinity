<?php

namespace Mintreu\LaravelCommerinity\Services\CartService\Support;

use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelCommerinity\Models\VoucherCode;

trait HasVoucherCodeValidator
{
    /**
     * Validate a hydrated coupon model; no DB lookups here.
     */
    protected function validateCouponCode(VoucherCode|string|null $voucher, ?Model $user = null): bool
    {
        if (!($voucher instanceof VoucherCode)) {
            $this->setError('Coupon code not found.');
            return false;
        }

        if (method_exists($voucher, 'isActive') && !$voucher->isActive()) {
            $this->setError('Coupon code not processable!');
            return false;
        }

        if (method_exists($voucher, 'isExpired') && $voucher->isExpired()) {
            $this->setError('Coupon code expired!');
            return false;
        }

        if (is_int($voucher->coupon_usage_limit) && is_int($voucher->times_used) &&
            $voucher->times_used >= $voucher->coupon_usage_limit) {
            $this->setError('Coupon usage limit reached.');
            return false;
        }

        if ($user && is_int($voucher->usage_per_user) && $voucher->usage_per_user > 0) {
            $timesByUser = (int) $voucher->usageByUser($user);
            if ($timesByUser >= $voucher->usage_per_user) {
                $this->setError('Coupon usage limit reached for this account.');
                return false;
            }
        }

        $this->couponCode = $voucher->code;
        return $this->getErrors() === null;
    }

    protected function sessionCouponKey(): string
    {
        return config('laravel-commerinity.cart.coupon.session_key', 'cart.coupon');
    }

    protected function putCouponInSession(string $code): void
    {
        session()->put($this->sessionCouponKey(), $code);
        session()->save();
    }

    protected function getCouponFromSession(): ?string
    {
        $code = session()->get($this->sessionCouponKey());
        return is_string($code) && $code !== '' ? $code : null;
    }

    protected function forgetCouponInSession(): void
    {
        session()->forget($this->sessionCouponKey());
        session()->save();
    }
}

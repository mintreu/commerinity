<?php

declare(strict_types=1);
namespace App\Services\Ecommerce\CartService\Support;

use App\Models\Ecommerce\VoucherCode;
use Illuminate\Database\Eloquent\Model;

trait HasVoucherCodeValidator
{
    /**
     * Set and validate voucher/coupon code
     */
    public function setCouponCode(string|VoucherCode $voucherCode): void
    {
        if (is_string($voucherCode)) {
            $voucherCode = VoucherCode::where('code', $voucherCode)->first();
            if (! $voucherCode) {
                $this->pushError('Invalid coupon code.');
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
     * Validate a coupon code instance
     */
    protected function validateCouponCode(VoucherCode|string|null $voucher, ?Model $user = null): bool
    {
        if (! ($voucher instanceof VoucherCode)) {
            $this->pushError('Coupon code not found.');

            return false;
        }

        if (method_exists($voucher, 'isActive') && ! $voucher->isActive()) {
            $this->pushError('Coupon code is not active.');

            return false;
        }

        if (method_exists($voucher, 'isExpired') && $voucher->isExpired()) {
            $this->pushError('Coupon code has expired.');

            return false;
        }

        $globalLimit = $voucher->getEffectiveUsageLimit();
        if ($globalLimit > 0 && $voucher->times_used >= $globalLimit) {
            $this->pushError('Coupon usage limit reached.');

            return false;
        }

        if ($user) {
            $perUserLimit = $voucher->getEffectiveUsagePerUser();
            if ($perUserLimit > 0) {
                $timesByUser = (int) $voucher->usageByUser($user);
                if ($timesByUser >= $perUserLimit) {
                    $this->pushError('You have already used this coupon the maximum number of times.');

                    return false;
                }
            }
        }

        return $this->resolveErrorState() === null;
    }

    protected function sessionCouponKey(): string
    {
        return config('cart.coupon.session_key', 'cart.coupon');
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

    public function loadCouponFromSession(): void
    {
        $code = $this->getCouponFromSession();
        if ($code) {
            $this->couponCode = $code;
            $this->validCoupon = true;
        }
    }

    public function clearCoupon(): void
    {
        $this->couponCode = null;
        $this->validCoupon = false;
        $this->forgetCouponInSession();
    }

    public function hasCoupon(): bool
    {
        return $this->validCoupon && ! empty($this->couponCode);
    }

    protected function pushError(string $message): void
    {
        if (method_exists($this, 'setError')) {
            $this->setError($message);
            return;
        }

        if (property_exists($this, 'cartService') && $this->cartService) {
            $this->cartService->setError($message);
        }
    }

    protected function resolveErrorState(): ?string
    {
        if (method_exists($this, 'getErrors')) {
            return $this->getErrors();
        }

        if (property_exists($this, 'cartService') && $this->cartService) {
            return $this->cartService->getErrors();
        }

        return null;
    }
}

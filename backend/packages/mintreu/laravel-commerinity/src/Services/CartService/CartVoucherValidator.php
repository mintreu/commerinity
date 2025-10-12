<?php

namespace Mintreu\LaravelCommerinity\Services\CartService;

use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelCommerinity\Casts\VoucherConditionMatchingCast;
use Mintreu\LaravelCommerinity\Models\Voucher;
use Mintreu\LaravelCommerinity\Models\VoucherCode;
use Mintreu\LaravelCommerinity\Services\CartService\Support\HasVoucherCodeValidator;
use Mintreu\LaravelCommerinity\Services\CartService\Support\HasVoucherConditionLogic;
use Mintreu\LaravelCommerinity\Services\CartService\Support\HasVoucherValidationAttributes;

class CartVoucherValidator
{
    use HasVoucherCodeValidator,HasVoucherValidationAttributes,HasVoucherConditionLogic;

    protected CartService $cartService;
    protected ?string $couponCode = null;
    protected ?VoucherCode $voucherCode = null;
    protected ?Model $customer = null;
    protected ?Voucher $voucher = null;
    protected bool $verified = false;
    protected bool $validAttribute = false;
    protected Model $cartable;       // morphable (Product, Service, etc.)

    /**
     * @param CartService $cartService
     * @param string|null $couponCode
     * @param Model|null $customer
     */
    public function __construct(CartService $cartService,?string $couponCode,?Model $customer = null)
    {
        $this->cartService = $cartService;
        $this->couponCode = $couponCode;
        $this->voucherCode = VoucherCode::with(['voucher','usages'])->where('code',$this->couponCode)->first();
        $this->customer = $customer;
        $this->voucher = $this->voucherCode?->voucher;
    }


    public static function make(CartService $cartService,?string $couponCode = null,?Model $customer = null): static
    {
        return new static($cartService,$couponCode,$customer);
    }

    public function isValid():bool
    {
        return $this->verified;
    }

    public function validate(Model $cartable):bool
    {
        $this->cartable = $cartable;
        if ($this->validateCouponCode($this->voucherCode,$this->customer))
        {
            if (empty($this->voucher->conditions))
            {
                return true;
            }

            if ($this->checkVoucherConditionValidation())
            {
                return $this->verified;
            }


        }
        return false;
    }

    public function getCoupon()
    {
        return $this->couponCode;
    }

    protected function checkVoucherConditionValidation():bool
    {

        $validConditionCount = 0;

        foreach ($this->voucher->conditions as $condition) {
            if (! empty($this->errors)) {
                // immediate return if error found
                return false;
            }

            if ($this->voucher->condition_type == VoucherConditionMatchingCast::MATCH_ALL) {
                if (! $this->validateCondition($condition)) {
                    // Return false if Single Condition Failed
                    return false;
                }
                $validConditionCount++;
            }

            if ($this->voucher->condition_type == VoucherConditionMatchingCast::MATCH_ANY) {
                if ($this->validateCondition($condition)) {
                    $validConditionCount++;
                    return true;
                }

                return false;
            }

        }

        // Validate Valid Condition Count
        if ($this->voucher->condition_type == VoucherConditionMatchingCast::MATCH_ALL) {
            return $validConditionCount == count($this->voucher->conditions);
        } else {
            return $validConditionCount > 0;
        }
    }



    private function validateCondition(array $condition): bool
    {
        $attributeValue = $this->getAttributeValue($condition, $this->cartable);


        if (empty($attributeValue)) {
            $this->cartService->setError($condition['attribute']."'s value not resolved");
            $this->validAttribute = false;
        } else {
            $this->validAttribute = true;
        }

        if ($this->validateVoucherConditionLogic($condition,$attributeValue))
        {
            $this->validAttribute = true;
        }else{
            $this->cartService->setError($condition['attribute']."'s value not resolved");
            $this->validAttribute = false;
        }


        return is_null($this->cartService->getErrors());
    }



}

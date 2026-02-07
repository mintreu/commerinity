<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService;

use App\Casts\ConditionMatchingCast;
use App\Models\Ecommerce\Voucher;
use App\Models\Ecommerce\VoucherCode;
use App\Services\Ecommerce\CartService\Support\HasVoucherCodeValidator;
use App\Services\Ecommerce\CartService\Support\HasVoucherConditionLogic;
use App\Services\Ecommerce\CartService\Support\HasVoucherValidationAttributes;
use Illuminate\Database\Eloquent\Model;

final class CartVoucherValidator
{
    use HasVoucherCodeValidator;
    use HasVoucherConditionLogic;
    use HasVoucherValidationAttributes;

    protected CartService $cartService;
    protected ?string $couponCode;
    protected ?VoucherCode $voucherCode;
    protected ?Voucher $voucher;
    protected ?Model $customer;
    protected bool $verified = false;
    protected Model $cartable;
    protected array $conditionResults = [];
    protected array $conditionErrors = [];
    protected ?string $validationMessage = null;

    public function __construct(CartService $cartService, ?string $couponCode, ?Model $customer = null, array $meta = [])
    {
        $this->cartService = $cartService;
        $this->couponCode = $couponCode;
        $this->customer = $customer;
        $this->meta = $meta;
        $this->voucherCode = $couponCode
            ? VoucherCode::with(['voucher', 'usages'])->where('code', $couponCode)->first()
            : null;
        $this->voucher = $this->voucherCode?->voucher;
    }

    public static function make(CartService $cartService, ?string $couponCode, ?Model $customer = null, array $meta = []): self
    {
        return new self($cartService, $couponCode, $customer, $meta);
    }

    public function validate(Model $cartable): bool
    {
        $this->cartable = $cartable;
        $this->conditionResults = [];
        $this->conditionErrors = [];
        $this->validationMessage = null;

        if (empty($this->couponCode) || ! $this->voucherCode || ! $this->voucher) {
            $this->validationMessage = 'Coupon code is not valid.';
            return false;
        }

        if (! $this->validateCouponCode($this->voucherCode, $this->customer)) {
            $this->validationMessage = $this->cartService->getErrors() ?? 'Coupon code is not valid.';
            return false;
        }

        if (empty($this->voucher->conditions)) {
            $this->verified = true;

            return true;
        }

        return $this->verifyConditions();
    }

    public function isValid(): bool
    {
        return $this->verified;
    }

    protected function verifyConditions(): bool
    {
        $matches = $this->voucher->condition_type ?? ConditionMatchingCast::MATCH_ALL;
        $results = [];
        $hasMatch = false;
        $hardError = false;

        foreach ($this->voucher->conditions as $condition) {
            $attributeValue = $this->getAttributeValue($condition, $this->cartable);
            $passed = $this->validateVoucherConditionLogic($condition, $attributeValue);
            $results[] = $passed;

            $this->conditionResults[] = [
                'attribute' => $condition['attribute'] ?? null,
                'operator' => $condition['operator'] ?? null,
                'expected' => $condition['value'] ?? null,
                'actual' => $attributeValue,
                'passed' => $passed,
            ];

            $error = $this->cartService->getErrors();
            if ($error) {
                if ($this->isHardValidationError($error)) {
                    $hardError = true;
                    $this->conditionErrors[] = $error;
                    break;
                }

                if ($matches === ConditionMatchingCast::MATCH_ANY) {
                    $this->cartService->setError(null);
                }
            }

            if ($matches === ConditionMatchingCast::MATCH_ANY && $passed === true) {
                $hasMatch = true;
                $this->cartService->setError(null);
                break;
            }

            if ($matches === ConditionMatchingCast::MATCH_ALL && $passed === false) {
                break;
            }
        }

        if ($hardError) {
            $this->verified = false;

            return $this->verified;
        }

        $this->verified = $matches === ConditionMatchingCast::MATCH_ANY
            ? $hasMatch
            : $matches->evaluate($results);

        if (! $this->verified) {
            $this->validationMessage = $this->buildFailureMessage();
            if (! $this->cartService->getErrors()) {
                $this->cartService->setError($this->validationMessage);
            }
        }

        return $this->verified;
    }

    private function isHardValidationError(string $error): bool
    {
        return str_starts_with($error, 'Invalid operator')
            || str_contains($error, 'attribute type mismatch')
            || str_contains($error, 'value must be scalar')
            || str_contains($error, 'value must be an array');
    }

    private function buildFailureMessage(): string
    {
        $message = $this->validationMessage;
        if ($message) {
            return $message;
        }

        $cartMin = $this->findConditionByAttribute('cart|subTotal');
        if ($cartMin && isset($cartMin['expected']) && isset($cartMin['actual'])) {
            if (is_numeric($cartMin['expected']) && is_numeric($cartMin['actual']) && $cartMin['actual'] < $cartMin['expected']) {
                return 'Cart total does not meet the minimum required for this coupon.';
            }
        }

        $qtyMin = $this->findConditionByAttribute('cart|totalQuantity');
        if ($qtyMin && isset($qtyMin['expected']) && isset($qtyMin['actual'])) {
            if (is_numeric($qtyMin['expected']) && is_numeric($qtyMin['actual']) && $qtyMin['actual'] < $qtyMin['expected']) {
                return 'Cart quantity does not meet the minimum required for this coupon.';
            }
        }

        return 'Coupon code is not applicable to the selected items.';
    }

    private function findConditionByAttribute(string $attribute): ?array
    {
        foreach ($this->conditionResults as $result) {
            if (($result['attribute'] ?? null) === $attribute) {
                return $result;
            }
        }

        return null;
    }

    public function getConditionResults(): array
    {
        return $this->conditionResults;
    }

    public function getConditionErrors(): array
    {
        return $this->conditionErrors;
    }

    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }

    public function getCoupon(): ?string
    {
        return $this->couponCode;
    }

    public function getVoucher(): ?Voucher
    {
        return $this->voucher;
    }
}

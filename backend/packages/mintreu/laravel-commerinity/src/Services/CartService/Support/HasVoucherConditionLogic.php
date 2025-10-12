<?php

namespace Mintreu\LaravelCommerinity\Services\CartService\Support;

use Mintreu\LaravelMoney\LaravelMoney;

trait HasVoucherConditionLogic
{
    protected bool $isValid = false;

    protected function validateVoucherConditionLogic(array $condition, $attributeValue):bool
    {
        return $this->validateWithOperator($condition,$attributeValue);
    }

    private function validateWithOperator(array $condition, $attributeValue):bool
    {
        $operator = $condition['operator'];
        match ($operator) {
            '==' => $this->getEqual($condition, $attributeValue),
            '!=' => $this->getNotEqual($condition, $attributeValue),
            '<=' => $this->getLessThanOrEqual($condition, $attributeValue),
            '>' => $this->getGreaterThan($condition, $attributeValue),
            '>=' => $this->getGreaterThanOrEqual($condition, $attributeValue),
            '{}' => $this->getIn($condition, $attributeValue),
            '!{}' => $this->getNotIn($condition, $attributeValue),
            default => $this->cartService->setError('Invalid operator for voucher condition validation : '.$operator),
        };

        return $this->isValid && is_null($this->cartService->getErrors());
    }

    private function getEqual(array $condition, $attributeValue): void
    {
        if ($attributeValue instanceof LaravelMoney)
        {
            $this->isValid = $attributeValue->sameAs($condition['value']);
        }else{
            if (is_array($condition['value']) && ! is_array($attributeValue)) {
                $this->cartService->setError('Condition '.$condition['attribute'].' Attribute type not matched!');
            } else {
                if (is_array($condition['value']) && is_array($attributeValue)) {
                    $this->isValid = ! empty(array_intersect($condition['value'], $attributeValue));
                }

                if (! is_array($condition['value']) && is_array($attributeValue)) {
                    $this->isValid = (\count($attributeValue) == 1 && array_shift($attributeValue) == $condition['value']);
                }

                if (! is_array($condition['value']) && ! is_array($attributeValue)) {
                    $this->isValid = ($attributeValue == $condition['value']);
                }
            }
        }

        if (! $this->isValid) {
            $this->cartService->setError($condition['attribute'].': value '.$condition['value'].' must be equal with '.$attributeValue);
        }
    }

    private function getNotEqual(array $condition, $attributeValue): void
    {
        if ($attributeValue instanceof LaravelMoney) {
            $this->isValid = ! $attributeValue->sameAs($condition['value']);
        }else{
            if (is_array($condition['value']) && ! is_array($attributeValue)) {
                $this->cartService->setError('Condition '.$condition['attribute'].' Attribute type not matched!');
            } else {
                if (is_array($condition['value']) && is_array($attributeValue)) {
                    $this->isValid = empty(array_intersect($condition['value'], $attributeValue));
                }

                if (! is_array($condition['value']) && is_array($attributeValue)) {
                    $this->isValid = \count($attributeValue) == 1 && array_shift($attributeValue) != $condition['value'];
                }

                if (! is_array($condition['value']) && ! is_array($attributeValue)) {
                    $this->isValid = $attributeValue != $condition['value'];
                }
            }
        }

        if (! $this->isValid) {
            $this->cartService->setError($condition['attribute'].': value '.$condition['value'].' must be not equal with '.$attributeValue);
        }
    }

    private function getLessThanOrEqual(array $condition, $attributeValue): void
    {
        if (! is_scalar($attributeValue) && ! ($attributeValue instanceof LaravelMoney)) {
            $this->cartService->setError($condition['attribute'].' value must be scalar type');
        }
        if (empty($this->cartService->getErrors())) {
            if ($attributeValue instanceof LaravelMoney) {
                $this->isValid = $attributeValue->lessThanOrEqual((new LaravelMoney($condition['value'])));
            } else {
                $this->isValid = $attributeValue <= $condition['value'];
            }
        }

        if (! $this->isValid) {
            $this->cartService->setError($condition['attribute'].': value '.$attributeValue.' must be less than or equal with conditions  '.$condition['attribute'].': '.$condition['value']);
        }
    }

    private function getGreaterThan(array $condition, $attributeValue): void
    {
        if (! ($attributeValue instanceof LaravelMoney) && ! is_scalar($attributeValue)) {
            $this->cartService->setError($condition['attribute'].' value must be scalar type');
        }

        if ($attributeValue instanceof LaravelMoney) {
            $this->isValid = $attributeValue->greaterThan((new LaravelMoney($condition['value'])));
        } else {
            $this->isValid = $attributeValue > $condition['value'];
        }

        if (! $this->isValid && empty($this->cartService->getErrors())) {
            $this->cartService->setError($condition['attribute'].': value '.$attributeValue.' must be greater than with conditions  '.$condition['attribute'].': '.$condition['value']);
        }
    }

    private function getGreaterThanOrEqual(array $condition, $attributeValue): void
    {
        if (! ($attributeValue instanceof LaravelMoney) && ! is_scalar($attributeValue)) {
            $this->cartService->setError($condition['attribute'].' value must be scalar type');
        }

        if ($attributeValue instanceof LaravelMoney) {
            $this->isValid = $attributeValue->greaterThanOrEqual((new LaravelMoney($condition['value'])));
        } else {
            $this->isValid = $attributeValue >= $condition['value'];
        }

        if (! $this->isValid && empty($this->cartService->getErrors())) {
            $this->cartService->setError($condition['attribute'].': value '.$condition['value'].' and must be equal or greater than item '.$condition['attribute'].': '.$attributeValue);
        }
    }

    private function getIn(array $condition, $attributeValue): void
    {
        $this->getNotIn($condition, $attributeValue);
    }

    private function getNotIn(array $condition, $attributeValue): void
    {
        if (is_scalar($attributeValue) && is_array($condition['value'])) {
            foreach ($condition['value'] as $item) {
                if (stripos($attributeValue, $item) !== false) {
                    $this->isValid = true;
                }
            }
        } elseif (is_array($condition['value'])) {
            if (! is_array($attributeValue)) {
                $this->cartService->setError($condition['attribute'].' value must be an array');
            }
            $this->isValid = ! empty(array_intersect($condition['value'], $attributeValue));
            if (! $this->isValid) {
                $this->cartService->setError($condition['attribute'].($condition['operator'] == '{}') ? 'must contain ' : 'must not contain '.implode(',', $condition['value']).' get '.$attributeValue);
            }
        } else {
            if (is_array($attributeValue)) {
                $this->isValid = self::validateArrayValues($attributeValue, $condition['value']);
                if (! $this->isValid) {
                    $this->cartService->setError($condition['attribute'].' array values validation failed!');
                }
            } else {
                $this->isValid = strpos($attributeValue, $condition['value']) !== false;
                if (! $this->isValid) {
                    $this->cartService->setError($condition['attribute'].($condition['operator'] == '{}') ? 'must contain ' : 'must not contain '.implode(',', $condition['value']).' get '.$attributeValue);
                }
            }
        }
    }

    private static function validateArrayValues(array $attributeValue, string $conditionValue): bool
    {
        if (in_array($conditionValue, $attributeValue, true) === true) {
            return true;
        }
        foreach ($attributeValue as $subValue) {
            if (is_array($subValue)) {
                if (self::validateArrayValues($subValue, $conditionValue) === true) {
                    return true;
                }
            }
        }
        return false;
    }
}

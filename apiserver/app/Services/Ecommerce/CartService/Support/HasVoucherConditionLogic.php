<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService\Support;

trait HasVoucherConditionLogic
{
    protected bool $isValid = false;

    protected function validateVoucherConditionLogic(array $condition, mixed $attributeValue): bool
    {
        return $this->validateWithOperator($condition, $attributeValue);
    }

    private function validateWithOperator(array $condition, mixed $attributeValue): bool
    {
        $operator = $condition['operator'] ?? '=';
        match ($operator) {
            '==' => $this->getEqual($condition, $attributeValue),
            '!=' => $this->getNotEqual($condition, $attributeValue),
            '<=' => $this->getLessThanOrEqual($condition, $attributeValue),
            '>' => $this->getGreaterThan($condition, $attributeValue),
            '>=' => $this->getGreaterThanOrEqual($condition, $attributeValue),
            '{}' => $this->getIn($condition, $attributeValue),
            '!{}' => $this->getNotIn($condition, $attributeValue),
            default => $this->cartService->setError('Invalid operator for voucher condition validation: '.$operator),
        };

        return $this->isValid && $this->cartService->getErrors() === null;
    }

    private function getEqual(array $condition, mixed $attributeValue): void
    {
        $value = $condition['value'];
        if (is_array($value) && ! is_array($attributeValue)) {
            $this->cartService->setError('Condition '.$condition['attribute'].' attribute type mismatch');
        } else {
            if (is_array($value) && is_array($attributeValue)) {
                $this->isValid = ! empty(array_intersect($value, $attributeValue));
            }

            if (! is_array($value) && is_array($attributeValue)) {
                $this->isValid = count($attributeValue) === 1 && array_shift($attributeValue) == $value;
            }

            if (! is_array($value) && ! is_array($attributeValue)) {
                $this->isValid = $attributeValue == $value;
            }
        }

        // Do not set error for normal mismatches; validator will set a generic error later.
    }

    private function getNotEqual(array $condition, mixed $attributeValue): void
    {
        $value = $condition['value'];
        if (is_array($value) && ! is_array($attributeValue)) {
            $this->cartService->setError('Condition '.$condition['attribute'].' attribute type mismatch');
        } else {
            if (is_array($value) && is_array($attributeValue)) {
                $this->isValid = empty(array_intersect($value, $attributeValue));
            }

            if (! is_array($value) && is_array($attributeValue)) {
                $this->isValid = count($attributeValue) === 1 && array_shift($attributeValue) != $value;
            }

            if (! is_array($value) && ! is_array($attributeValue)) {
                $this->isValid = $attributeValue != $value;
            }
        }

        // Do not set error for normal mismatches; validator will set a generic error later.
    }

    private function getLessThanOrEqual(array $condition, mixed $attributeValue): void
    {
        if (! is_scalar($attributeValue)) {
            $this->cartService->setError($condition['attribute'].' value must be scalar');
        }

        if (empty($this->cartService->getErrors())) {
            $this->isValid = $attributeValue <= $condition['value'];
        }

        // Do not set error for normal mismatches; validator will set a generic error later.
    }

    private function getGreaterThan(array $condition, mixed $attributeValue): void
    {
        if (! is_scalar($attributeValue)) {
            $this->cartService->setError($condition['attribute'].' value must be scalar');
        }

        $this->isValid = $attributeValue > $condition['value'];

        // Do not set error for normal mismatches; validator will set a generic error later.
    }

    private function getGreaterThanOrEqual(array $condition, mixed $attributeValue): void
    {
        if (! is_scalar($attributeValue)) {
            $this->cartService->setError($condition['attribute'].' value must be scalar');
        }

        $this->isValid = $attributeValue >= $condition['value'];

        // Do not set error for normal mismatches; validator will set a generic error later.
    }

    private function getIn(array $condition, mixed $attributeValue): void
    {
        $this->getNotIn($condition, $attributeValue);
    }

    private function getNotIn(array $condition, mixed $attributeValue): void
    {
        $value = $condition['value'];

        if (is_scalar($attributeValue) && is_array($value)) {
            foreach ($value as $item) {
                if (stripos((string) $attributeValue, (string) $item) !== false) {
                    $this->isValid = true;
                }
            }
        } elseif (is_array($value)) {
            if (! is_array($attributeValue)) {
                $this->cartService->setError($condition['attribute'].' value must be an array');
            }
            $this->isValid = ! empty(array_intersect($value, (array) $attributeValue));
        } elseif (is_array($attributeValue)) {
            $this->isValid = in_array((string) $value, $attributeValue, true);
        } else {
            $this->isValid = strpos((string) $attributeValue, (string) $value) !== false;
        }

        // Do not set error for normal mismatches; validator will set a generic error later.
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ecommerce\Products\Support;

final class ProductFilterOptions
{
    /**
     * Normalize dynamic form state to ProductManager format:
     * [filter_id => [option_id, ...]]
     */
    public static function normalize(array $filterOptions): array
    {
        $normalized = [];

        foreach ($filterOptions as $filterId => $options) {
            $selectedOptions = [];

            if (is_array($options)) {
                foreach ($options as $optionId => $value) {
                    if (is_bool($value)) {
                        if ($value) {
                            $selectedOptions[] = (string) $optionId;
                        }

                        continue;
                    }

                    if (filled($value)) {
                        $selectedOptions[] = (string) $value;
                    }
                }
            } elseif ($options !== null && $options !== '') {
                $selectedOptions[] = (string) $options;
            }

            if (! empty($selectedOptions)) {
                $normalized[(string) $filterId] = array_values(array_unique($selectedOptions));
            }
        }

        return $normalized;
    }
}


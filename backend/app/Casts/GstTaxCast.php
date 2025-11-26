<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use App\Enums\GstTaxSlab;

class GstTaxCast implements CastsAttributes
{
    /**
     * Cast the stored value to the GstTaxSlab enum.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?GstTaxSlab
    {
        if (is_null($value)) {
            return null;
        }

        return GstTaxSlab::tryFrom((int) $value) ?? GstTaxSlab::GST_0;
    }

    /**
     * Prepare the value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof GstTaxSlab) {
            return $value->value;
        }

        // Attempt to create from integer or string value
        $enum = GstTaxSlab::tryFrom((int) $value);

        return $enum?->value;
    }
}

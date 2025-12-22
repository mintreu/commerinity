<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->transformLaravelMoney($this->resource);
    }

    /**
     * Recursively transform LaravelMoney instances to formatted strings.
     *
     * @param mixed $data
     * @return mixed
     */
    private function transformLaravelMoney($data)
    {
        // Handle LaravelMoney instances
        if ($data instanceof LaravelMoney) {
            return $data->formatted();
        }

        // Handle arrays recursively
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = $this->transformLaravelMoney($value);
            }
            return $result;
        }

        // Handle objects (but not Eloquent models to avoid deep recursion issues)
        if (is_object($data) && !$data instanceof \Illuminate\Database\Eloquent\Model) {
            $reflection = new \ReflectionObject($data);

            // Skip non-public properties to avoid issues
            if ($reflection->hasMethod('toArray')) {
                return $this->transformLaravelMoney($data->toArray());
            }
        }

        // Return scalar values and models as-is
        return $data;
    }
}

<?php

namespace Mintreu\Toolkit\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

trait HasPackageModelFactory
{
    use HasFactory;

    /**
     * Dynamically resolve the factory class for the model.
     *
     * Assumes the factory class follows this convention:
     * - Located in the same package under `Database\Factories`
     * - Named as `ModelNameFactory`
     *
     * @throws \RuntimeException if the factory class is not found
     */
    protected static function newFactory()
    {
        $modelClass = static::class;

        // Determine the base namespace before "Models\"
        $baseNamespace = Str::before($modelClass, 'Models\\');

        // Extract class name only, e.g., "Category"
        $modelName = class_basename($modelClass);

        // Construct expected factory class path
        $factoryClass = $baseNamespace . 'Database\\Factories\\' . $modelName . 'Factory';

        if (!class_exists($factoryClass)) {
            throw new \RuntimeException("Factory class [$factoryClass] not found for [$modelClass]");
        }

        return $factoryClass::new();
    }
}

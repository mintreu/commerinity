<?php

namespace Mintreu\Toolkit\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UniqueCodeHelper
{
    /**
     * Generate a unique random code with optional prefix/suffix.
     * - If $modelOrClass is a Model instance: assigns to $column and optionally saves.
     * - If $modelOrClass is a class-string<Model>: returns the generated code only.
     */
    public static function makeUniqueCode(
        Model|string $modelOrClass,
        string $column,
        int $length = 8,
        ?string $prefix = null,
        ?string $suffix = null,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        self::assertLength($length);
        $modelClass = self::resolveModelClass($modelOrClass);
        $instance = self::resolveInstance($modelOrClass);
        self::assertFillableIfInstance($instance, $column);

        // If instance already has value, return it unchanged.
        if ($instance && !empty($instance->{$column})) {
            return (string) $instance->{$column};
        }

        do {
            $candidate = ($prefix ?? '') . Str::random($length) . ($suffix ?? '');
        } while (self::codeExists($modelClass, $column, $candidate, $ignoreKeyValue));

        if ($instance && $assignWhenModel) {
            $instance->{$column} = $candidate;
            if ($saveWhenModel) {
                $instance->save();
            }
        }

        return $candidate;
    }

    public static function makeUniqueCodeUpper(
        Model|string $modelOrClass,
        string $column,
        int $length = 8,
        ?string $prefix = null,
        ?string $suffix = null,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        $code = self::makeUniqueCode($modelOrClass, $column, $length, $prefix, $suffix, false, false, $ignoreKeyValue);
        $upper = strtoupper($code);
        self::maybeAssign($modelOrClass, $column, $upper, $assignWhenModel, $saveWhenModel);
        return $upper;
    }

    /**
     * Refresh a code using time-based seed padded to a fixed length.
     * Always assigns on Model and can save; for class-string returns the new code.
     */
    public static function refreshUniqueCode(
        Model|string $modelOrClass,
        string $column = 'uuid',
        int $length = 16,
        ?string $prefix = null,
        ?string $suffix = null,
        bool $saveWhenModel = true
    ): string {
        self::assertLength($length);
        $modelClass = self::resolveModelClass($modelOrClass);
        $instance = self::resolveInstance($modelOrClass);
        self::assertFillableIfInstance($instance, $column);

        do {
            $base = now()->format('YmdHisv');
            $candidate = ($prefix ?? '') . $base . ($suffix ?? '');
            $candidate = substr(str_pad($candidate, $length, Str::random($length)), 0, $length);
        } while (self::codeExists($modelClass, $column, $candidate, self::currentKey($instance)));

        if ($instance) {
            $instance->{$column} = $candidate;
            if ($saveWhenModel) {
                $instance->save();
            }
        }

        return $candidate;
    }

    /**
     * ULID: time-ordered unique identifier; optionally trimmed length.
     */
    public static function makeUniqueUlid(
        Model|string $modelOrClass,
        string $column,
        int $length = 26,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        self::assertLength($length);
        $modelClass = self::resolveModelClass($modelOrClass);
        $instance = self::resolveInstance($modelOrClass);
        self::assertFillableIfInstance($instance, $column);

        if ($instance && !empty($instance->{$column})) {
            return (string) $instance->{$column};
        }

        do {
            $ulid = (string) Str::ulid();
            $candidate = substr($ulid, 0, $length);
        } while (self::codeExists($modelClass, $column, $candidate, $ignoreKeyValue));

        self::maybeAssign($modelOrClass, $column, $candidate, $assignWhenModel, $saveWhenModel);
        return $candidate;
    }

    public static function makeUniqueUlidUpper(
        Model|string $modelOrClass,
        string $column,
        int $length = 26,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        $code = self::makeUniqueUlid($modelOrClass, $column, $length, false, false, $ignoreKeyValue);
        $upper = strtoupper($code);
        self::maybeAssign($modelOrClass, $column, $upper, $assignWhenModel, $saveWhenModel);
        return $upper;
    }

    /**
     * UUID v4 (hyphens stripped, then trimmed); optionally uppercased variant.
     */
    public static function makeUniqueUuid(
        Model|string $modelOrClass,
        string $column,
        int $length = 32, // 36 with hyphens, 32 without
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        self::assertLength($length);
        $modelClass = self::resolveModelClass($modelOrClass);
        $instance = self::resolveInstance($modelOrClass);
        self::assertFillableIfInstance($instance, $column);

        if ($instance && !empty($instance->{$column})) {
            return (string) $instance->{$column};
        }

        do {
            $uuid = str_replace('-', '', (string) Str::uuid());
            $candidate = substr($uuid, 0, $length);
        } while (self::codeExists($modelClass, $column, $candidate, $ignoreKeyValue));

        self::maybeAssign($modelOrClass, $column, $candidate, $assignWhenModel, $saveWhenModel);
        return $candidate;
    }

    public static function makeUniqueUuidUpper(
        Model|string $modelOrClass,
        string $column,
        int $length = 32,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        $code = self::makeUniqueUuid($modelOrClass, $column, $length, false, false, $ignoreKeyValue);
        $upper = strtoupper($code);
        self::maybeAssign($modelOrClass, $column, $upper, $assignWhenModel, $saveWhenModel);
        return $upper;
    }

    /**
     * Initials + random part (requires a Model instance to derive initials from attribute).
     */
    public static function makeUniqueInitialsCode(
        Model|string $modelOrClass,
        string $column,
        string $nameAttribute = 'name',
        int $randomLength = 6,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        self::assertLength($randomLength);
        $instance = self::resolveInstance($modelOrClass);
        if (!$instance) {
            throw new InvalidArgumentException('makeUniqueInitialsCode requires a Model instance to read initials.');
        }
        self::assertFillableIfInstance($instance, $column);

        if (!empty($instance->{$column})) {
            return (string) $instance->{$column};
        }

        $initials = collect(explode(' ', (string) ($instance->{$nameAttribute} ?? '')))
            ->map(fn ($n) => substr($n, 0, 1))
            ->implode('');

        $modelClass = $instance::class;

        do {
            $candidate = $initials . Str::random($randomLength);
        } while (self::codeExists($modelClass, $column, $candidate, $ignoreKeyValue));

        if ($assignWhenModel) {
            $instance->{$column} = $candidate;
            if ($saveWhenModel) {
                $instance->save();
            }
        }

        return $candidate;
    }

    public static function makeUniqueInitialsCodeUpper(
        Model|string $modelOrClass,
        string $column,
        string $nameAttribute = 'name',
        int $randomLength = 6,
        bool $assignWhenModel = true,
        bool $saveWhenModel = false,
        ?string $ignoreKeyValue = null
    ): string {
        $code = self::makeUniqueInitialsCode($modelOrClass, $column, $nameAttribute, $randomLength, false, false, $ignoreKeyValue);
        $upper = strtoupper($code);
        self::maybeAssign($modelOrClass, $column, $upper, $assignWhenModel, $saveWhenModel);
        return $upper;
    }

    // ————————————————————————
    // Internals
    // ————————————————————————

    private static function resolveModelClass(Model|string $modelOrClass): string
    {
        return is_string($modelOrClass) ? $modelOrClass : $modelOrClass::class;
    }

    private static function resolveInstance(Model|string $modelOrClass): ?Model
    {
        return $modelOrClass instanceof Model ? $modelOrClass : null;
    }

    private static function assertFillableIfInstance(?Model $instance, string $column): void
    {
        if (!$instance) return;
        $fillable = $instance->getFillable();
        if (!in_array($column, $fillable, true)) {
            throw new InvalidArgumentException("Invalid column name: {$column}. Ensure it is in \$fillable.");
        }
    }

    private static function assertLength(int $length): void
    {
        if ($length < 1) {
            throw new InvalidArgumentException('Length must be greater than 0.');
        }
    }

    private static function codeExists(string $modelClass, string $column, string $value, ?string $ignoreKeyValue = null): bool
    {
        /** @var Model $probe */
        $probe = new $modelClass();
        $pk = $probe->getKeyName();

        $query = $modelClass::query()->where($column, $value);
        if (!empty($ignoreKeyValue)) {
            $query->where($pk, '!=', $ignoreKeyValue);
        }
        return $query->exists();
    }

    private static function maybeAssign(Model|string $modelOrClass, string $column, string $value, bool $assign, bool $save): void
    {
        $instance = self::resolveInstance($modelOrClass);
        if ($instance && $assign) {
            $instance->{$column} = $value;
            if ($save) {
                $instance->save();
            }
        }
    }

    private static function currentKey(?Model $instance): ?string
    {
        return $instance?->getKey();
    }
}

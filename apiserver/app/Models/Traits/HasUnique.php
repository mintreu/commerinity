<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * HasUnique Trait
 *
 * Provides methods to generate unique codes, UUIDs, and ULIDs
 * for model identification. Replaces manual random string generation.
 */
trait HasUnique
{
    /**
     * Sets a unique alphanumeric code for the given column.
     */
    public function setUniqueCode(string $columnName, int $length = 8, ?string $prefix = null, ?string $suffix = null): void
    {
        $this->validateColumnName($columnName);
        $this->validateLength($length);

        if (! empty($this->{$columnName})) {
            return;
        }

        do {
            $uniqueCode = ($prefix ?? '').Str::random($length).($suffix ?? '');
        } while ($this->codeExists($columnName, $uniqueCode));

        $this->{$columnName} = $uniqueCode;
    }

    /**
     * Returns an uppercase variant of setUniqueCode for convenience.
     */
    public function setUniqueCodeUpper(string $columnName, int $length = 8, ?string $prefix = null, ?string $suffix = null): void
    {
        $this->setUniqueCode($columnName, $length, $prefix, $suffix);
        $this->{$columnName} = strtoupper($this->{$columnName});
    }

    /**
     * Refreshes a unique code for the given column.
     */
    public function refreshUniqueCode(string $columnName = 'uuid', int $length = 16, ?string $prefix = null, ?string $suffix = null): void
    {
        $this->validateColumnName($columnName);
        $this->validateLength($length);

        do {
            $baseValue = now()->format('YmdHisv');
            $uniqueValue = ($prefix ?? '').$baseValue.($suffix ?? '');
            $uniqueValue = substr(str_pad($uniqueValue, $length, Str::random($length)), 0, $length);
        } while ($this->codeExists($columnName, $uniqueValue));

        $this->{$columnName} = $uniqueValue;
        $this->save();
    }

    /**
     * Sets a unique ULID with optional length trimming.
     */
    public function setUniqueUlid(string $columnName, int $length = 26): void
    {
        $this->validateColumnName($columnName);
        $this->validateLength($length);

        if (! empty($this->{$columnName})) {
            return;
        }

        do {
            $ulid = (string) Str::ulid();
            $this->{$columnName} = substr($ulid, 0, $length);
        } while ($this->codeExists($columnName, $this->{$columnName}));
    }

    /**
     * Sets uppercase ULID.
     */
    public function setUniqueUlidUpper(string $columnName, int $length = 26): void
    {
        $this->setUniqueUlid($columnName, $length);
        $this->{$columnName} = strtoupper($this->{$columnName});
    }

    /**
     * Sets a unique UUID with optional length trimming.
     */
    public function setUniqueUuid(string $columnName, int $length = 36): void
    {
        $this->validateColumnName($columnName);
        $this->validateLength($length);

        if (! empty($this->{$columnName})) {
            return;
        }

        do {
            $uuid = Str::uuid()->toString();
            $uuid = str_replace('-', '', $uuid);
            $this->{$columnName} = substr($uuid, 0, $length);
        } while ($this->codeExists($columnName, $this->{$columnName}));
    }

    /**
     * Sets uppercase UUID.
     */
    public function setUniqueUuidUpper(string $columnName, int $length = 36): void
    {
        $this->setUniqueUuid($columnName, $length);
        $this->{$columnName} = strtoupper($this->{$columnName});
    }

    /**
     * Sets a unique code based on initials + random string.
     */
    public function setUniqueInitialsCode(string $columnName, int $length = 6): void
    {
        $this->validateColumnName($columnName);
        $this->validateLength($length);

        if (! empty($this->{$columnName})) {
            return;
        }

        $initials = collect(explode(' ', $this->name))
            ->map(fn ($n) => substr($n, 0, 1))
            ->implode('');

        do {
            $randomPart = Str::random($length);
            $this->{$columnName} = $initials.$randomPart;
        } while ($this->codeExists($columnName, $this->{$columnName}));
    }

    /**
     * Sets uppercase initials code.
     */
    public function setUniqueInitialsCodeUpper(string $columnName, int $length = 6): void
    {
        $this->setUniqueInitialsCode($columnName, $length);
        $this->{$columnName} = strtoupper($this->{$columnName});
    }

    /**
     * Optimized uniqueness check.
     */
    private function codeExists(string $columnName, string $value): bool
    {
        return static::where($columnName, $value)->exists();
    }

    /**
     * Validate column exists in fillable.
     */
    private function validateColumnName(string $columnName): void
    {
        if (! in_array($columnName, $this->getFillable(), true)) {
            throw new InvalidArgumentException("Invalid column name: {$columnName}");
        }
    }

    /**
     * Validate length is positive.
     */
    private function validateLength(int $length): void
    {
        if ($length < 1) {
            throw new InvalidArgumentException('Length must be greater than 0.');
        }
    }
}

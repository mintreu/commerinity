<?php

namespace Mintreu\Toolkit\Traits;

use Illuminate\Support\Str;
use InvalidArgumentException;

trait HasUnique
{
    /**
     * Sets a unique alphanumeric code for the given column.
     */
    public function setUniqueCode(string $column_name, int $length = 8, ?string $prefix = null, ?string $suffix = null): void
    {
        $this->validateColumnName($column_name);
        $this->validateLength($length);

        if (!empty($this->{$column_name})) {
            return;
        }

        do {
            $uniqueCode = ($prefix ?? '') . Str::random($length) . ($suffix ?? '');
        } while ($this->codeExists($column_name, $uniqueCode));

        $this->{$column_name} = $uniqueCode;
    }

    /**
     * Returns an uppercase variant of setUniqueCode for convenience.
     */
    public function setUniqueCodeUpper(string $column_name, int $length = 8, ?string $prefix = null, ?string $suffix = null): void
    {
        $this->setUniqueCode($column_name, $length, $prefix, $suffix);
        $this->{$column_name} = strtoupper($this->{$column_name});
    }

    /**
     * Refreshes a unique code for the given column.
     */
    public function refreshUniqueCode(string $column_name = 'uuid', int $length = 16, ?string $prefix = null, ?string $suffix = null): void
    {
        $this->validateColumnName($column_name);
        $this->validateLength($length);

        do {
            $baseValue = now()->format('YmdHisv');
            $uniqueValue = ($prefix ?? '') . $baseValue . ($suffix ?? '');
            $uniqueValue = substr(str_pad($uniqueValue, $length, Str::random($length)), 0, $length);
        } while ($this->codeExists($column_name, $uniqueValue));

        $this->{$column_name} = $uniqueValue;
        $this->save();
    }

    /**
     * Sets a unique ULID with optional length trimming.
     */
    public function setUniqueUlid(string $column_name, int $length = 26): void
    {
        $this->validateColumnName($column_name);
        $this->validateLength($length);

        if (!empty($this->{$column_name})) return;

        do {
            $ulid = (string) Str::ulid();
            $this->{$column_name} = substr($ulid, 0, $length);
        } while ($this->codeExists($column_name, $this->{$column_name}));
    }

    public function setUniqueUlidUpper(string $column_name, int $length = 26): void
    {
        $this->setUniqueUlid($column_name, $length);
        $this->{$column_name} = strtoupper($this->{$column_name});
    }

    /**
     * Sets a unique UUID with optional length trimming.
     */
    public function setUniqueUuid(string $column_name, int $length = 36): void
    {
        $this->validateColumnName($column_name);
        $this->validateLength($length);

        if (!empty($this->{$column_name})) return;

        do {
            $uuid = Str::uuid()->toString();
            $uuid = str_replace('-', '', $uuid); // keep default format, no forced upper
            $this->{$column_name} = substr($uuid, 0, $length);
        } while ($this->codeExists($column_name, $this->{$column_name}));
    }

    public function setUniqueUuidUpper(string $column_name, int $length = 36): void
    {
        $this->setUniqueUuid($column_name, $length);
        $this->{$column_name} = strtoupper($this->{$column_name});
    }

    /**
     * Sets a unique code based on initials + random string.
     */
    public function setUniqueInitialsCode(string $column_name, int $length = 6): void
    {
        $this->validateColumnName($column_name);
        $this->validateLength($length);

        if (!empty($this->{$column_name})) return;

        $initials = collect(explode(' ', $this->name))->map(fn($n) => substr($n, 0, 1))->implode('');

        do {
            $randomPart = Str::random($length);
            $this->{$column_name} = $initials . $randomPart;
        } while ($this->codeExists($column_name, $this->{$column_name}));
    }

    public function setUniqueInitialsCodeUpper(string $column_name, int $length = 6): void
    {
        $this->setUniqueInitialsCode($column_name, $length);
        $this->{$column_name} = strtoupper($this->{$column_name});
    }

    /**
     * Optimized uniqueness check.
     */
    private function codeExists(string $column_name, string $value): bool
    {
        return static::where($column_name, $value)->exists();
    }

    private function validateColumnName(string $column_name): void
    {
        if (!in_array($column_name, $this->getFillable(), true)) {
            throw new InvalidArgumentException("Invalid column name: {$column_name}");
        }
    }

    private function validateLength(int $length): void
    {
        if ($length < 1) throw new InvalidArgumentException("Length must be greater than 0.");
    }
}

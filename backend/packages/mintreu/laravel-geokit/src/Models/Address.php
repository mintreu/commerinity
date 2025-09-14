<?php

namespace Mintreu\LaravelGeokit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\{Country, State, Block};
use Illuminate\Database\Eloquent\Builder;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;

class Address extends Model
{
    use HasPackageModelFactory;

    protected $fillable = [
        'uuid',
        'title',
        'pickup_location',
        'person_name',
        'person_email',
        'person_mobile',
        'alternate_contact',
        'type',
        'address_1',
        'landmark',
        'city',
        'block_id',
        'postal_code',
        'state_code',
        'default',
        'priority',
        'country_code',
        'addressable_id',
        'addressable_type',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'default' => 'bool',
            'priority' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'type' => AddressTypeCast::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($address) {
            if (is_null($address->uuid)) {
                $address->uuid = Str::ulid();
            }

            if (is_null($address->type)) {
                $address->type = AddressTypeCast::HOME->value; // assuming you have a HOME constant
            }

            if (is_null($address->title)) {
                $label = method_exists($address->type, 'getLabel')
                    ? $address->type->getLabel()
                    : ucfirst($address->type);
                $address->title = 'My ' . $label . ' Address';
            }

            if (is_null($address->country_code)) {
                $address->country_code = config('laravel-geokit.default.country');
            }
        });
    }

    // Relationships

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso_code_2');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id', 'id');
    }

    // Scopes

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('default', true);
    }

    // Helpers

    // ✅ Regular method (instance-level)
    public function hasPostal(string $postalCode): bool
    {
        return $this->postal_code === $postalCode;
    }

// ✅ Query scope (static-level)
    public function scopeHasPostal(Builder $query, string $postalCode): Builder
    {
        return $query->where('postal_code', $postalCode);
    }


    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_1,
            $this->village,
            $this->landmark,
            $this->city,
            optional($this->block)->name,
            optional($this->state)->name,
            $this->postal_code,
            optional($this->country)->name,
        ])
            ->filter()
            ->join(', ');
    }
}

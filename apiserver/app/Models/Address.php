<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Observers\AddressObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[ObservedBy(AddressObserver::class)]
class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'person_name',
        'person_email',
        'person_mobile',
        'alternate_contact',
        'type',
        'address_1',
        'address_2',
        'landmark',
        'city',
        'postal_code',
        'block_id',
        'state_code',
        'country_code',
        'latitude',
        'longitude',
        'default',
        'priority',
        'pickup_location',
    ];

    protected function casts(): array
    {
        return [
            'type' => \App\Casts\AddressTypeCast::class,
            'default' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'priority' => 'integer',
            'block_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Address $address) {
            if (! $address->uuid) {
                $address->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the owning addressable model (User, etc.)
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the block this address belongs to
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get the state this address belongs to
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }

    /**
     * Get the country this address belongs to
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso_code_2');
    }

    /**
     * Scope for standalone addresses (warehouses, stores)
     */
    public function scopeStandalone($query)
    {
        return $query->whereNull('addressable_id')
            ->whereNull('addressable_type');
    }

    /**
     * Scope for warehouse/hub addresses
     */
    public function scopeWarehouses($query)
    {
        return $query->standalone()->where('type', \App\Casts\AddressTypeCast::HUB);
    }

    /**
     * Scope for store/service point addresses
     */
    public function scopeStores($query)
    {
        return $query->standalone()->where('type', \App\Casts\AddressTypeCast::SERVICE_POINT);
    }

    /**
     * Scope for user addresses
     */
    public function scopeUserAddresses($query)
    {
        return $query->whereMorphedTo('addressable', User::class);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get default addresses
     */
    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }

    /**
     * Get full formatted address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_1,
            $this->address_2,
            $this->landmark,
            $this->city,
            $this->state?->name,
            $this->postal_code,
            $this->country?->name,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if address has geo-coordinates
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Get coordinates from block if address doesn't have them
     */
    public function getEffectiveCoordinates(): ?array
    {
        if ($this->hasCoordinates()) {
            return [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ];
        }

        if ($this->block && $this->block->hasCoordinates()) {
            return [
                'latitude' => $this->block->latitude,
                'longitude' => $this->block->longitude,
            ];
        }

        return null;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

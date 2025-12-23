<?php

declare(strict_types=1);

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'isd_code',
        'address_format',
        'postcode_required',
        'locale',
        'region',
        'timezone',
        'timezone_diff',
        'currency',
        'flag',
        'exchange_rate',
        'multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'array',
            'postcode_required' => 'boolean',
            'is_active' => 'boolean',
            'multiplier' => 'float',
            'isd_code' => 'integer',
        ];
    }

    /**
     * Get all states for this country
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get all addresses using this country
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(\App\Models\Address::class, 'country_code', 'iso_code_2');
    }

    /**
     * Scope to get only active countries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by region
     */
    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }
}

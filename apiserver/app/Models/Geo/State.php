<?php

declare(strict_types=1);

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'country_id',
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
        ];
    }

    /**
     * Get the country this state belongs to
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all blocks in this state
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'state_code', 'code');
    }

    /**
     * Get all districts in this state.
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get all addresses in this state
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(\App\Models\Address::class, 'state_code', 'code');
    }

    /**
     * Scope to filter by country
     */
    public function scopeByCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to filter by country code
     */
    public function scopeByCountryCode($query, string $countryCode)
    {
        return $query->whereHas('country', function ($q) use ($countryCode) {
            $q->where('iso_code_2', $countryCode);
        });
    }
}

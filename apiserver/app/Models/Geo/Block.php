<?php

declare(strict_types=1);

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'district_name',
        'state_code',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Get the state this block belongs to
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }

    /**
     * Get all addresses in this block
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(\App\Models\Address::class);
    }

    /**
     * Scope to filter by state
     */
    public function scopeByState($query, string $stateCode)
    {
        return $query->where('state_code', $stateCode);
    }

    /**
     * Scope to filter by district
     */
    public function scopeByDistrict($query, string $districtName)
    {
        return $query->where('district_name', $districtName);
    }

    /**
     * Check if block has geo-coordinates
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}

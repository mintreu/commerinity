<?php

declare(strict_types=1);

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    /** @use HasFactory<\Database\Factories\Geo\DistrictFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'state_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'state_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(\App\Models\Address::class);
    }
}

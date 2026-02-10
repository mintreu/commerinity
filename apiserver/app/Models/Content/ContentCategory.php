<?php

declare(strict_types=1);

namespace App\Models\Content;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ContentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'type' => ContentType::class,
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(ContentPost::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, ContentType $type): Builder
    {
        return $query->where('type', $type);
    }
}

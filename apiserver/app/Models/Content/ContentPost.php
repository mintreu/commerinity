<?php

declare(strict_types=1);

namespace App\Models\Content;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ContentPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'author_name',
        'seo_title',
        'seo_description',
        'published_at',
        'is_published',
    ];

    protected $casts = [
        'type' => ContentType::class,
        'published_at' => 'datetime',
        'is_published' => 'bool',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $inner) {
                $inner->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now());
            });
    }

    public function scopeOfType(Builder $query, ContentType $type): Builder
    {
        return $query->where('type', $type);
    }
}

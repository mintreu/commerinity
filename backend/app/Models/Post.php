<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Mintreu\Toolkit\Traits\HasUnique;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory,HasUnique,InteractsWithMedia;


    protected $fillable = [
        'name',
        'url',
        'description',
        'category_id',
        'author_id',
        'author_type',
        'status',
        'status_feedback',
    ];

    protected $casts = [
        'status' => PublishableStatusCast::class
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('displayImage')
            ->useFallbackUrl('https://placehold.co/800x300?text=Image\nNot Found')
        ;
        $this->addMediaCollection('bannerImage')
            //->useFallbackUrl('https://placehold.co/600x400?text=Banner\nImage')
        ;
    }

    public function getRouteKeyName(): string
    {
        return 'url';
    }


    /** Polymorphic author relationship */
    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    /** Category relationship */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


}

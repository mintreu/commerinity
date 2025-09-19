<?php

namespace Mintreu\LaravelPenpress\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'prefix',
        'url',
        'title',
        'content',
        'layout',
        'template',
        'meta',
        'sections',
        'custom_css',
        'custom_js',
        'status',
        'order',
    ];

    protected $casts = [
        'meta' => 'array',
        'sections' => 'array',
        'status' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($page) {
            $page->url = $page->prefix ? "{$page->prefix}/{$page->slug}" : $page->slug;
        });
    }
}

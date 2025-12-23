<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class HelpdeskTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tickable',
        'description',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'tickable' => 'boolean',
            'active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'topic_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(HelpdeskFaq::class, 'topic_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeTickable($query)
    {
        return $query->where('tickable', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}

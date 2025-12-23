<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HelpdeskFaq extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'question',
        'answer',
        'topic_id',
        'active',
        'order',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTopic::class, 'topic_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('question');
    }

    public function getRouteKeyName(): string
    {
        return 'url';
    }
}

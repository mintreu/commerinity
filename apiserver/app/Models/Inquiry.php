<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Contact form inquiry model.
 *
 * Handles both general (user) inquiries and business inquiries.
 */
class Inquiry extends Model
{
    /** @use HasFactory<\Database\Factories\InquiryFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'is_business' => false,
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'company_name',
        'address',
        'website',
        'is_business',
        'status',
        'replied_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_business' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    /**
     * Scope to get pending inquiries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get business inquiries.
     */
    public function scopeBusiness($query)
    {
        return $query->where('is_business', true);
    }

    /**
     * Scope to get general user inquiries.
     */
    public function scopeGeneral($query)
    {
        return $query->where('is_business', false);
    }

    /**
     * Mark as replied.
     */
    public function markAsReplied(): void
    {
        $this->update([
            'status' => 'replied',
            'replied_at' => now(),
        ]);
    }
}

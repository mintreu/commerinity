<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\KycStatusCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Kyc extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'kyc_type',
        'company_name',
        'company_type',
        'pan_number',
        'aadhaar_number',
        'gst_number',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => KycStatusCast::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function kycable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', KycStatusCast::PENDING->value);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', KycStatusCast::APPROVED->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', KycStatusCast::REJECTED->value);
    }

    public function scopePersonal($query)
    {
        return $query->where('kyc_type', 'personal');
    }

    public function scopeBusiness($query)
    {
        return $query->where('kyc_type', 'business');
    }

    public function isPending(): bool
    {
        return $this->status === KycStatusCast::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === KycStatusCast::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === KycStatusCast::REJECTED;
    }

    public function approve(?int $reviewerId = null): bool
    {
        return $this->update([
            'status' => KycStatusCast::APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'rejection_reason' => null,
        ]);
    }

    public function reject(string $reason, ?int $reviewerId = null): bool
    {
        return $this->update([
            'status' => KycStatusCast::REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'rejection_reason' => $reason,
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf'])
            ->maxFilesize(5 * 1024 * 1024);
    }
}

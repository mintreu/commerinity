<?php

declare(strict_types=1);

namespace App\Models\Recruitment;

use App\Casts\RecruitmentRoleCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\RecruitmentTypeCast;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Recruitment extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected static function newFactory(): \Database\Factories\RecruitmentFactory
    {
        return \Database\Factories\RecruitmentFactory::new();
    }

    protected $fillable = [
        'uuid',
        'slug',
        'title',
        'description',
        'role',
        'location',
        'employment_type',
        'vacancy',
        'open_date',
        'close_date',
        'is_payable',
        'fees',
        'requirements',
        'benefits',
        'eligibility',
        'status',
        'status_feedback',
    ];

    protected function casts(): array
    {
        return [
            'role' => RecruitmentRoleCast::class,
            'employment_type' => RecruitmentTypeCast::class,
            'status' => RecruitmentStatusCast::class,
            'open_date' => 'date',
            'close_date' => 'date',
            'is_payable' => 'boolean',
            'fees' => 'integer',
            'vacancy' => 'integer',
            'requirements' => 'array',
            'benefits' => 'array',
            'eligibility' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Recruitment $recruitment) {
            if (! $recruitment->uuid) {
                $recruitment->uuid = Str::uuid()->toString();
            }

            if (! $recruitment->slug) {
                $recruitment->slug = Str::slug($recruitment->title).'-'.Str::random(6);
            }
        });
    }

    // ========================================
    // Media Collections
    // ========================================

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('display_image')
            ->singleFile()
            ->useFallbackUrl('https://placehold.co/600x400?text='.urlencode($this->title ?? 'Job'));

        $this->addMediaCollection('info_pdf')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);
    }

    // ========================================
    // Relationships
    // ========================================

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    // ========================================
    // Accessors
    // ========================================

    public function getFeesFormattedAttribute(): string
    {
        return MoneyService::format($this->fees);
    }

    public function getFeesInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->fees);
    }

    public function getIsOpenAttribute(): bool
    {
        if ($this->status !== RecruitmentStatusCast::Published) {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->open_date && $this->open_date->gt($now)) {
            return false;
        }

        if ($this->close_date && $this->close_date->lt($now)) {
            return false;
        }

        return true;
    }

    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }

    public function getAcceptedCountAttribute(): int
    {
        return $this->applications()->where('status', 'accepted')->count();
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', RecruitmentStatusCast::Published);
    }

    public function scopeOpen(Builder $query): Builder
    {
        $now = now()->startOfDay();

        return $query->published()
            ->where(function ($q) use ($now) {
                $q->whereNull('open_date')->orWhere('open_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('close_date')->orWhere('close_date', '>=', $now);
            });
    }

    public function scopeForRole(Builder $query, RecruitmentRoleCast $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopePayable(Builder $query): Builder
    {
        return $query->where('is_payable', true);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class VoucherCode extends Model
{
    use HasFactory;

    public const TYPE_PUBLIC = 0;

    public const TYPE_PRIVATE = 1;

    public const TYPE_SINGLE_USE = 2;

    protected $fillable = [
        'voucher_id',
        'code',
        'is_primary',
        'coupon_usage_limit',
        'usage_per_user',
        'times_used',
        'starts_from',
        'ends_till',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'coupon_usage_limit' => 'integer',
            'usage_per_user' => 'integer',
            'times_used' => 'integer',
            'type' => 'integer',
            'starts_from' => 'date',
            'ends_till' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VoucherCode $code): void {
            if (empty($code->code)) {
                $code->code = self::generateUniqueCode();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Parent voucher
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Orders that used this code
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'voucher', 'code');
    }

    /**
     * Usage tracking per user
     */
    public function usages(): MorphToMany
    {
        return $this->morphedByMany(
            config('auth.providers.users.model'),
            'userable',
            'voucher_code_usages',
            'voucher_code_id',
            'userable_id'
        )->withPivot('times_used')->withTimestamps();
    }

    // ==================== SCOPES ====================

    /**
     * Active codes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q): void {
            $q->whereNull('starts_from')
                ->orWhere('starts_from', '<=', now());
        })->where(function ($q): void {
            $q->whereNull('ends_till')
                ->orWhere('ends_till', '>=', now());
        });
    }

    /**
     * Primary codes only
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /**
     * Public codes only
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PUBLIC);
    }

    /**
     * Available codes (not fully used)
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function ($q): void {
            $q->where('coupon_usage_limit', 0) // Unlimited or use voucher limit
                ->orWhereColumn('times_used', '<', 'coupon_usage_limit');
        });
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if this code is currently active
     */
    public function isActive(): bool
    {
        $now = now();

        // Check own validity period
        if ($this->starts_from && $now->lt($this->starts_from)) {
            return false;
        }

        if ($this->ends_till && $now->gt($this->ends_till)) {
            return false;
        }

        // Also check parent voucher
        return $this->voucher && $this->voucher->isActive();
    }

    /**
     * Check if this code is expired
     */
    public function isExpired(): bool
    {
        if ($this->ends_till && now()->gt($this->ends_till)) {
            return true;
        }

        return $this->voucher && $this->voucher->isExpired();
    }

    /**
     * Get effective usage limit (own or from voucher)
     */
    public function getEffectiveUsageLimit(): int
    {
        if ($this->coupon_usage_limit > 0) {
            return $this->coupon_usage_limit;
        }

        return $this->voucher?->coupon_usage_limit ?? 0;
    }

    /**
     * Get effective usage per user limit
     */
    public function getEffectiveUsagePerUser(): int
    {
        if ($this->usage_per_user > 0) {
            return $this->usage_per_user;
        }

        return $this->voucher?->usage_per_customer ?? 1;
    }

    /**
     * Get usage count for a specific user
     */
    public function usageByUser(Model $user): int
    {
        return $this->usages()
            ->wherePivot('userable_id', $user->getKey())
            ->wherePivot('userable_type', $user->getMorphClass())
            ->first()?->pivot->times_used ?? 0;
    }

    /**
     * Check if this code can be used by a specific user
     */
    public function canBeUsedByUser(Model $user): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        // Check global usage limit
        $globalLimit = $this->getEffectiveUsageLimit();
        if ($globalLimit > 0 && $this->times_used >= $globalLimit) {
            return false;
        }

        // Check per-user usage limit
        $perUserLimit = $this->getEffectiveUsagePerUser();
        if ($perUserLimit > 0 && $this->usageByUser($user) >= $perUserLimit) {
            return false;
        }

        // Check parent voucher
        if ($this->voucher && ! $this->voucher->canBeUsed()) {
            return false;
        }

        return true;
    }

    /**
     * Check if this is a single-use code
     */
    public function isSingleUse(): bool
    {
        return $this->type === self::TYPE_SINGLE_USE;
    }

    /**
     * Check if this is a public code
     */
    public function isPublic(): bool
    {
        return $this->type === self::TYPE_PUBLIC;
    }

    /**
     * Check if this is a private code
     */
    public function isPrivate(): bool
    {
        return $this->type === self::TYPE_PRIVATE;
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(): bool
    {
        $result = $this->increment('times_used');

        // Also increment parent voucher usage
        if ($this->voucher) {
            $this->voucher->incrementUsage();
        }

        return $result;
    }

    /**
     * Record usage for a specific user
     */
    public function recordUsageByUser(Model $user): void
    {
        $existingUsage = $this->usages()
            ->wherePivot('userable_id', $user->getKey())
            ->wherePivot('userable_type', $user->getMorphClass())
            ->first();

        if ($existingUsage) {
            $this->usages()->updateExistingPivot($user->getKey(), [
                'times_used' => $existingUsage->pivot->times_used + 1,
            ]);
        } else {
            $this->usages()->attach($user->getKey(), [
                'userable_type' => $user->getMorphClass(),
                'times_used' => 1,
            ]);
        }

        $this->incrementUsage();
    }

    /**
     * Generate a unique coupon code
     */
    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the type label
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_PUBLIC => 'Public',
            self::TYPE_PRIVATE => 'Private',
            self::TYPE_SINGLE_USE => 'Single Use',
            default => 'Unknown',
        };
    }
}

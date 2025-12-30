<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AdPlacementCast;
use App\Casts\AdTypeCast;
use Database\Factories\AdvertisementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Advertisement Model
 *
 * Manages ad spaces across the site. Supports:
 * - Native ads (self-hosted images/videos)
 * - Google AdSense
 * - Facebook Audience Network
 * - Amazon Associates
 * - Custom HTML/JS code
 * - Affiliate partner ads
 */
class Advertisement extends Model implements HasMedia
{
    /** @use HasFactory<AdvertisementFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'placement',
        'block',
        'is_active',
        'is_premium',
        'starts_at',
        'ends_at',
        'title',
        'description',
        'link_url',
        'link_text',
        'open_in_new_tab',
        'ad_code',
        'ad_unit_id',
        'affiliate_network',
        'affiliate_tracking_id',
        'position',
        'display_pages',
        'exclude_pages',
        'show_to_guests',
        'show_to_members',
        'target_user_types',
        'width',
        'height',
        'is_responsive',
        'impressions',
        'clicks',
        'last_impression_at',
        'last_click_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => AdTypeCast::class,
            'placement' => AdPlacementCast::class,
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'open_in_new_tab' => 'boolean',
            'display_pages' => 'array',
            'exclude_pages' => 'array',
            'show_to_guests' => 'boolean',
            'show_to_members' => 'boolean',
            'target_user_types' => 'array',
            'is_responsive' => 'boolean',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'last_impression_at' => 'datetime',
            'last_click_at' => 'datetime',
        ];
    }

    // ========================================
    // Media Collections
    // ========================================

    public function registerMediaCollections(): void
    {
        // Main ad image (for native ads)
        $this->addMediaCollection('ad_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        // Mobile-specific image
        $this->addMediaCollection('ad_image_mobile')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        // Video ad
        $this->addMediaCollection('ad_video')
            ->singleFile()
            ->acceptsMimeTypes(['video/mp4', 'video/webm']);
    }

    // ========================================
    // Relationships
    // ========================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Only active ads
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Only ads currently within schedule
     */
    public function scopeScheduled(Builder $query): Builder
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $now);
        });
    }

    /**
     * Filter by placement
     */
    public function scopeForPlacement(Builder $query, AdPlacementCast|string $placement): Builder
    {
        $value = $placement instanceof AdPlacementCast ? $placement->value : $placement;

        return $query->where('placement', $value);
    }

    /**
     * Filter by type
     */
    public function scopeOfType(Builder $query, AdTypeCast|string $type): Builder
    {
        $value = $type instanceof AdTypeCast ? $type->value : $type;

        return $query->where('type', $value);
    }

    /**
     * Filter by block
     */
    public function scopeForBlock(Builder $query, string $block): Builder
    {
        return $query->where('block', $block);
    }

    /**
     * Premium ads only
     */
    public function scopePremium(Builder $query): Builder
    {
        return $query->where('is_premium', true);
    }

    /**
     * Visible to guests
     */
    public function scopeForGuests(Builder $query): Builder
    {
        return $query->where('show_to_guests', true);
    }

    /**
     * Visible to members
     */
    public function scopeForMembers(Builder $query): Builder
    {
        return $query->where('show_to_members', true);
    }

    /**
     * Order by position
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if ad is currently active and within schedule
     */
    public function isDisplayable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Check if ad should be shown to a specific user
     */
    public function shouldShowTo(?User $user): bool
    {
        if (! $this->isDisplayable()) {
            return false;
        }

        // Guest check
        if (! $user && ! $this->show_to_guests) {
            return false;
        }

        // Member check
        if ($user && ! $this->show_to_members) {
            return false;
        }

        // User type targeting
        if ($user && ! empty($this->target_user_types)) {
            // TODO: Check user type against target_user_types
        }

        return true;
    }

    /**
     * Record an impression
     */
    public function recordImpression(): void
    {
        $this->increment('impressions');
        $this->update(['last_impression_at' => now()]);
    }

    /**
     * Record a click
     */
    public function recordClick(): void
    {
        $this->increment('clicks');
        $this->update(['last_click_at' => now()]);
    }

    /**
     * Get click-through rate (CTR)
     */
    public function getCtr(): float
    {
        if ($this->impressions === 0) {
            return 0.0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    /**
     * Get the ad image URL
     */
    public function getImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('ad_image') ?: null;
    }

    /**
     * Get the mobile ad image URL
     */
    public function getMobileImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('ad_image_mobile') ?: null;
    }

    /**
     * Get recommended size for this ad placement
     */
    public function getRecommendedSize(): array
    {
        if ($this->width && $this->height) {
            return ['width' => $this->width, 'height' => $this->height];
        }

        return $this->placement->getSize();
    }

    /**
     * Check if this is a native ad (has uploaded content)
     */
    public function isNativeAd(): bool
    {
        return $this->type === AdTypeCast::NATIVE;
    }

    /**
     * Check if this is a third-party ad (Google, Facebook, etc.)
     */
    public function isThirdPartyAd(): bool
    {
        return in_array($this->type, [
            AdTypeCast::GOOGLE,
            AdTypeCast::FACEBOOK,
            AdTypeCast::AMAZON,
            AdTypeCast::CUSTOM_HTML,
        ]);
    }
}

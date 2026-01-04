<?php

declare(strict_types=1);

namespace App\Models\Membership;

use App\Contracts\Affiliate\CommissionTrigger;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MoneyService;
use App\Traits\HasTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * UserSubscription Model - Tracks user membership subscriptions
 *
 * Implements CommissionTrigger to trigger Affiliate commissions when subscription is activated.
 * Supports:
 * - New subscriptions (joining)
 * - Renewals
 * - Upgrades (stage progression)
 */
class UserSubscription extends Model implements CommissionTrigger
{
    use HasFactory;
    use HasTransaction;
    use SoftDeletes;

    // Amount column for HasTransaction trait
    public const TRANSACTION_AMOUNT_COLUMN = 'amount';

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_UPGRADED = 'upgraded';

    protected $fillable = [
        'uuid',
        'user_id',
        'stage_id',
        'level_id',
        'current_level_id',
        'level_achieved_at',
        'highest_level_id',
        'qualification_snapshot',
        'base_price',
        'discount',
        'tax_amount',
        'amount',
        'is_paid',
        'paid_at',
        'transaction_id',
        'wallet_id',
        'starts_at',
        'expires_at',
        'status',
        'previous_subscription_id',
        'sponsor_type',
        'sponsor_id',
        'personal_pv',
        'team_pv',
        'total_commission_earned',
        'current_month_commission',
        'last_renewed_at',
        'renewal_count',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'discount' => 'integer',
            'tax_amount' => 'integer',
            'amount' => 'integer',
            'total_commission_earned' => 'integer',
            'current_month_commission' => 'integer',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'level_achieved_at' => 'datetime',
            'last_renewed_at' => 'datetime',
            'qualification_snapshot' => 'array',
            'personal_pv' => 'integer',
            'team_pv' => 'integer',
            'renewal_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UserSubscription $subscription) {
            if (! $subscription->uuid) {
                $subscription->uuid = Str::uuid()->toString();
            }

            // Calculate pricing if not set
            if (! $subscription->amount && $subscription->stage_id) {
                $subscription->calculatePricing();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->user();
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function highestLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'highest_level_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function previousSubscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'previous_subscription_id');
    }

    public function nextSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'previous_subscription_id');
    }

    /**
     * Get the sponsor (who paid for this subscription)
     */
    public function sponsor(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================
    // CommissionTrigger Implementation
    // ========================================

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommissionableAmount(): int
    {
        return $this->amount;
    }

    public function getTriggeringUserId(): int
    {
        return $this->user_id;
    }

    public function getTriggerType(): string
    {
        if ($this->previous_subscription_id) {
            // Check if it's a renewal or upgrade
            $previous = $this->previousSubscription;
            if ($previous && $previous->stage_id !== $this->stage_id) {
                return 'upgrade';
            }

            return 'renewal';
        }

        return 'subscription';
    }

    public function getModel(): Model
    {
        return $this;
    }

    public function getCommissionContext(): array
    {
        return [
            'stage_id' => $this->stage_id,
            'level_id' => $this->level_id,
            'is_renewal' => $this->renewal_count > 0,
            'is_upgrade' => $this->previous_subscription_id !== null
                && $this->previousSubscription?->stage_id !== $this->stage_id,
            'subscription_type' => $this->getTriggerType(),
            'sponsor_type' => $this->sponsor_type,
            'sponsor_id' => $this->sponsor_id,
        ];
    }

    // ========================================
    // Pricing Methods
    // ========================================

    /**
     * Calculate pricing from stage
     */
    public function calculatePricing(): void
    {
        $stage = $this->stage ?? Stage::find($this->stage_id);
        if (! $stage) {
            return;
        }

        $this->base_price = $stage->base_price;
        $this->discount = $stage->discount;
        $this->tax_amount = $stage->tax_amount;
        $this->amount = $stage->price;
    }

    /**
     * Get price in rupees
     */
    public function getPriceInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->amount);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return MoneyService::format($this->amount);
    }

    // ========================================
    // Status Methods
    // ========================================

    /**
     * Activate subscription after payment
     */
    public function activate(?int $transactionId = null): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'is_paid' => true,
            'paid_at' => now(),
            'starts_at' => now(),
            'expires_at' => now()->addDays($this->level?->validity_days ?? 365),
            'transaction_id' => $transactionId ?? $this->transaction_id,
        ]);

        // Set initial level
        if (! $this->current_level_id) {
            $firstLevel = $this->stage?->getFirstLevel();
            if ($firstLevel) {
                $this->update([
                    'current_level_id' => $firstLevel->id,
                    'level_achieved_at' => now(),
                    'highest_level_id' => $firstLevel->id,
                ]);
            }
        }
    }

    /**
     * Mark subscription as expired
     */
    public function expire(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason,
        ]);
    }

    /**
     * Mark as upgraded (when user upgrades to next stage)
     */
    public function markAsUpgraded(): void
    {
        $this->update([
            'status' => self::STATUS_UPGRADED,
        ]);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->is_paid
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->expires_at && $this->expires_at->isPast());
    }

    // ========================================
    // Level Progression Methods
    // ========================================

    /**
     * Promote to next level within stage
     */
    public function promoteToLevel(Level $level, array $qualificationSnapshot = []): bool
    {
        // Verify level belongs to same stage
        if ($level->stage_id !== $this->stage_id) {
            return false;
        }

        // Verify it's actually a promotion
        $currentLevel = $this->currentLevel;
        if ($currentLevel && $level->level_number <= $currentLevel->level_number) {
            return false;
        }

        $this->update([
            'current_level_id' => $level->id,
            'level_achieved_at' => now(),
            'qualification_snapshot' => $qualificationSnapshot,
        ]);

        // Update highest level if needed
        $highestLevel = $this->highestLevel;
        if (! $highestLevel || $level->global_rank > $highestLevel->global_rank) {
            $this->update(['highest_level_id' => $level->id]);
        }

        return true;
    }

    /**
     * Get current level number (1-4)
     */
    public function getCurrentLevelNumber(): int
    {
        return $this->currentLevel?->level_number ?? 1;
    }

    /**
     * Get next level in progression
     */
    public function getNextLevel(): ?Level
    {
        return $this->currentLevel?->getNextLevel();
    }

    /**
     * Check if user can be promoted to next level
     */
    public function canPromoteToNextLevel(array $stats): bool
    {
        $nextLevel = $this->getNextLevel();
        if (! $nextLevel) {
            return false; // Already at max level
        }

        return $nextLevel->checkQualification($stats);
    }

    // ========================================
    // Commission Methods
    // ========================================

    /**
     * Add earned commission
     */
    public function addCommission(int $amountInPaisa): void
    {
        $this->increment('total_commission_earned', $amountInPaisa);
        $this->increment('current_month_commission', $amountInPaisa);
    }

    /**
     * Reset monthly commission (called by scheduler)
     */
    public function resetMonthlyCommission(): void
    {
        $this->update(['current_month_commission' => 0]);
    }

    // ========================================
    // Renewal Methods
    // ========================================

    /**
     * Renew subscription
     */
    public function renew(int $transactionId): self
    {
        // Create new subscription record
        $renewal = static::create([
            'user_id' => $this->user_id,
            'stage_id' => $this->stage_id,
            'level_id' => $this->level_id,
            'current_level_id' => $this->current_level_id,
            'highest_level_id' => $this->highest_level_id,
            'base_price' => $this->base_price,
            'discount' => $this->discount,
            'tax_amount' => $this->tax_amount,
            'amount' => $this->amount,
            'is_paid' => true,
            'paid_at' => now(),
            'transaction_id' => $transactionId,
            'starts_at' => $this->expires_at ?? now(),
            'expires_at' => ($this->expires_at ?? now())->addDays($this->level?->validity_days ?? 365),
            'status' => self::STATUS_ACTIVE,
            'previous_subscription_id' => $this->id,
            'renewal_count' => $this->renewal_count + 1,
            'personal_pv' => $this->personal_pv,
            'team_pv' => $this->team_pv,
        ]);

        // Mark current as expired
        $this->expire();

        return $renewal;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('is_paid', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeExpiring($query, int $daysAhead = 7)
    {
        return $query->active()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($daysAhead))
            ->where('expires_at', '>', now());
    }

    // ========================================
    // Static Helpers
    // ========================================

    /**
     * Get active subscription for user
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return static::active()->forUser($userId)->latest()->first();
    }

    /**
     * Check if user has active subscription
     */
    public static function hasActiveSubscription(int $userId): bool
    {
        return static::active()->forUser($userId)->exists();
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Sms;

use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Providers\Fast2SmsProvider;
use App\Services\Sms\Providers\LogSmsProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SMS Provider Model - Database-backed SMS provider configuration.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $driver
 * @property string|null $api_key
 * @property string|null $api_secret
 * @property string|null $sender_id
 * @property string|null $entity_id
 * @property array<string, mixed>|null $config
 * @property float $balance
 * @property float $per_sms_cost
 * @property float $min_balance_threshold
 * @property \Carbon\Carbon|null $balance_checked_at
 * @property \Carbon\Carbon|null $rate_valid_until
 * @property bool $is_active
 * @property bool $is_default
 * @property int $priority
 * @property bool $supports_dlt
 * @property bool $supports_otp
 * @property bool $supports_promotional
 * @property bool $supports_whatsapp
 * @property bool $supports_voice_otp
 * @property int $total_sent
 * @property int $total_delivered
 * @property int $total_failed
 * @property float $success_rate
 * @property \Carbon\Carbon|null $last_success_at
 * @property \Carbon\Carbon|null $last_failure_at
 * @property string|null $last_error
 * @property int $consecutive_failures
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class SmsProvider extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'driver',
        'api_key',
        'api_secret',
        'sender_id',
        'entity_id',
        'config',
        'balance',
        'per_sms_cost',
        'min_balance_threshold',
        'balance_checked_at',
        'rate_valid_until',
        'is_active',
        'is_default',
        'priority',
        'supports_dlt',
        'supports_otp',
        'supports_promotional',
        'supports_whatsapp',
        'supports_voice_otp',
        'total_sent',
        'total_delivered',
        'total_failed',
        'success_rate',
        'last_success_at',
        'last_failure_at',
        'last_error',
        'consecutive_failures',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'config' => 'array',
            'balance' => 'decimal:2',
            'per_sms_cost' => 'decimal:4',
            'min_balance_threshold' => 'decimal:2',
            'balance_checked_at' => 'datetime',
            'rate_valid_until' => 'datetime',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'priority' => 'integer',
            'supports_dlt' => 'boolean',
            'supports_otp' => 'boolean',
            'supports_promotional' => 'boolean',
            'supports_whatsapp' => 'boolean',
            'supports_voice_otp' => 'boolean',
            'total_sent' => 'integer',
            'total_delivered' => 'integer',
            'total_failed' => 'integer',
            'success_rate' => 'decimal:2',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'consecutive_failures' => 'integer',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * @return HasMany<SmsTemplate, $this>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(SmsTemplate::class);
    }

    /**
     * @return HasMany<SmsLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Get active providers ordered by priority.
     *
     * @param  Builder<SmsProvider>  $query
     * @return Builder<SmsProvider>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('priority');
    }

    /**
     * Get default provider.
     *
     * @param  Builder<SmsProvider>  $query
     * @return Builder<SmsProvider>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get providers that support OTP.
     *
     * @param  Builder<SmsProvider>  $query
     * @return Builder<SmsProvider>
     */
    public function scopeSupportsOtp(Builder $query): Builder
    {
        return $query->where('supports_otp', true);
    }

    /**
     * Get providers with sufficient balance.
     *
     * @param  Builder<SmsProvider>  $query
     * @return Builder<SmsProvider>
     */
    public function scopeWithSufficientBalance(Builder $query, float $required = 0): Builder
    {
        return $query->whereRaw('balance >= min_balance_threshold')
            ->when($required > 0, fn ($q) => $q->where('balance', '>=', $required));
    }

    /**
     * Get serviceable providers (active, configured, has balance).
     *
     * @param  Builder<SmsProvider>  $query
     * @return Builder<SmsProvider>
     */
    public function scopeServiceable(Builder $query): Builder
    {
        return $query->active()
            ->where('consecutive_failures', '<', 5) // Circuit breaker
            ->whereRaw('balance >= min_balance_threshold');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Check if provider is healthy (no recent failures).
     *
     * @return Attribute<bool, never>
     */
    protected function isHealthy(): Attribute
    {
        return Attribute::get(fn () => $this->consecutive_failures < 5);
    }

    /**
     * Get estimated SMS count that can be sent with current balance.
     *
     * @return Attribute<int, never>
     */
    protected function canSendCount(): Attribute
    {
        return Attribute::get(fn () => $this->per_sms_cost > 0
            ? (int) floor($this->balance / $this->per_sms_cost)
            : 0
        );
    }

    /**
     * Check if balance is low.
     *
     * @return Attribute<bool, never>
     */
    protected function isBalanceLow(): Attribute
    {
        return Attribute::get(fn () => $this->balance < $this->min_balance_threshold);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Create a provider driver instance.
     */
    public function createDriver(): SmsProviderInterface
    {
        $driver = match ($this->driver) {
            'fast2sms' => new Fast2SmsProvider(
                apiKey: $this->api_key,
                senderId: $this->sender_id,
                entityId: $this->entity_id,
                perSmsCost: (float) $this->per_sms_cost,
                minBalanceThreshold: (float) $this->min_balance_threshold,
            ),
            'log' => new LogSmsProvider,
            default => new LogSmsProvider,
        };

        $driver->setProviderModel($this);

        return $driver;
    }

    /**
     * Record successful send.
     */
    public function recordSuccess(int $count = 1, float $cost = 0): void
    {
        $this->increment('total_sent', $count);
        $this->increment('total_delivered', $count);
        $this->decrement('balance', $cost);
        $this->update([
            'last_success_at' => now(),
            'consecutive_failures' => 0,
        ]);

        $this->updateSuccessRate();
    }

    /**
     * Record failed send.
     */
    public function recordFailure(string $error, int $count = 1): void
    {
        $this->increment('total_sent', $count);
        $this->increment('total_failed', $count);
        $this->increment('consecutive_failures');
        $this->update([
            'last_failure_at' => now(),
            'last_error' => $error,
        ]);

        $this->updateSuccessRate();
    }

    /**
     * Update success rate percentage.
     */
    public function updateSuccessRate(): void
    {
        if ($this->total_sent > 0) {
            $rate = ($this->total_delivered / $this->total_sent) * 100;
            $this->update(['success_rate' => round($rate, 2)]);
        }
    }

    /**
     * Check if can send given count of SMS.
     */
    public function canSend(int $count = 1): bool
    {
        $required = $count * $this->per_sms_cost;

        return $this->is_active
            && $this->is_healthy
            && $this->balance >= $required;
    }

    /**
     * Reset circuit breaker (after manual intervention).
     */
    public function resetCircuitBreaker(): void
    {
        $this->update([
            'consecutive_failures' => 0,
            'last_error' => null,
        ]);
    }

    /**
     * Set as default provider.
     */
    public function setAsDefault(): void
    {
        // Remove default from all others
        static::query()->where('id', '!=', $this->id)->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Get usage statistics for analytics.
     *
     * @return array<string, mixed>
     */
    public function getUsageStats(): array
    {
        return [
            'total_sent' => $this->total_sent,
            'total_delivered' => $this->total_delivered,
            'total_failed' => $this->total_failed,
            'success_rate' => $this->success_rate,
            'balance' => $this->balance,
            'can_send_count' => $this->can_send_count,
            'is_healthy' => $this->is_healthy,
            'last_success_at' => $this->last_success_at?->toIso8601String(),
            'last_failure_at' => $this->last_failure_at?->toIso8601String(),
        ];
    }

    /**
     * Get monthly expense projection.
     *
     * @return array<string, mixed>
     */
    public function getMonthlyExpenseProjection(): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $daysInMonth = $now->daysInMonth;
        $daysPassed = $now->day;
        $daysRemaining = $daysInMonth - $daysPassed;

        // Get SMS sent this month from logs
        $monthlyStats = $this->logs()
            ->where('created_at', '>=', $startOfMonth)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(cost) as total_cost')
            ->selectRaw('COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered_count')
            ->first();

        $totalSentThisMonth = $monthlyStats->total_count ?? 0;
        $totalCostThisMonth = (float) ($monthlyStats->total_cost ?? 0);
        $deliveredThisMonth = $monthlyStats->delivered_count ?? 0;

        // Daily averages
        $dailyAverage = $daysPassed > 0 ? $totalSentThisMonth / $daysPassed : 0;
        $dailyCostAverage = $daysPassed > 0 ? $totalCostThisMonth / $daysPassed : 0;

        // Projections
        $projectedMonthlyCount = (int) round($dailyAverage * $daysInMonth);
        $projectedMonthlyCost = $dailyCostAverage * $daysInMonth;
        $projectedRemainingCost = $dailyCostAverage * $daysRemaining;

        // Balance analysis
        $daysBalanceWillLast = $dailyCostAverage > 0 ? (int) floor($this->balance / $dailyCostAverage) : 999;
        $balanceRunOutDate = $dailyCostAverage > 0 ? $now->copy()->addDays($daysBalanceWillLast) : null;
        $recommendedRecharge = max(0, $projectedRemainingCost - $this->balance);

        // Determine recharge tier for optimal pricing
        $rechargeTiers = [
            ['min' => 600000, 'cost' => 0.11],
            ['min' => 130000, 'cost' => 0.13],
            ['min' => 60000, 'cost' => 0.15],
            ['min' => 14000, 'cost' => 0.17],
            ['min' => 8000, 'cost' => 0.19],
            ['min' => 4000, 'cost' => 0.21],
            ['min' => 100, 'cost' => 0.25],
        ];

        $optimalTier = null;
        foreach ($rechargeTiers as $tier) {
            if ($recommendedRecharge >= $tier['min']) {
                $optimalTier = $tier;
                break;
            }
        }

        return [
            // Current period stats
            'period' => [
                'month' => $now->format('F Y'),
                'days_passed' => $daysPassed,
                'days_remaining' => $daysRemaining,
                'days_in_month' => $daysInMonth,
            ],

            // Actual usage
            'actual' => [
                'sms_sent' => $totalSentThisMonth,
                'sms_delivered' => $deliveredThisMonth,
                'total_cost' => round($totalCostThisMonth, 2),
                'daily_average_count' => round($dailyAverage, 1),
                'daily_average_cost' => round($dailyCostAverage, 2),
            ],

            // Projections
            'projected' => [
                'monthly_sms_count' => $projectedMonthlyCount,
                'monthly_cost' => round($projectedMonthlyCost, 2),
                'remaining_cost' => round($projectedRemainingCost, 2),
            ],

            // Balance analysis
            'balance' => [
                'current' => round((float) $this->balance, 2),
                'can_send_count' => $this->can_send_count,
                'days_will_last' => $daysBalanceWillLast,
                'run_out_date' => $balanceRunOutDate?->format('Y-m-d'),
                'is_sufficient_for_month' => $this->balance >= $projectedRemainingCost,
            ],

            // Recharge recommendation
            'recharge' => [
                'recommended_amount' => round($recommendedRecharge, 2),
                'optimal_tier' => $optimalTier,
                'current_per_sms_cost' => $this->per_sms_cost,
            ],

            // Health
            'health' => [
                'is_healthy' => $this->is_healthy,
                'is_balance_low' => $this->is_balance_low,
                'success_rate' => $this->success_rate,
                'consecutive_failures' => $this->consecutive_failures,
            ],
        ];
    }
}

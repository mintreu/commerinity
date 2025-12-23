<?php

declare(strict_types=1);

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * SMS Log Model - Tracks all sent messages.
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $sms_provider_id
 * @property string $provider_slug
 * @property string $recipient
 * @property string $message
 * @property string $message_type
 * @property int|null $sms_template_id
 * @property string|null $template_code
 * @property array<string, mixed>|null $variables
 * @property int|null $user_id
 * @property string|null $loggable_type
 * @property int|null $loggable_id
 * @property string|null $request_id
 * @property string|null $message_id
 * @property string $status
 * @property string|null $delivery_status
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $delivered_at
 * @property \Carbon\Carbon|null $failed_at
 * @property float $cost
 * @property int $segments
 * @property string|null $error_code
 * @property string|null $error_message
 * @property int $retry_count
 * @property int $max_retries
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $source
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SmsLog extends Model
{
    use HasFactory;
    use HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REJECTED = 'rejected';

    public const TYPE_OTP = 'otp';

    public const TYPE_TRANSACTIONAL = 'transactional';

    public const TYPE_PROMOTIONAL = 'promotional';

    public const TYPE_ALERT = 'alert';

    protected $fillable = [
        'uuid',
        'sms_provider_id',
        'provider_slug',
        'recipient',
        'message',
        'message_type',
        'sms_template_id',
        'template_code',
        'variables',
        'user_id',
        'loggable_type',
        'loggable_id',
        'request_id',
        'message_id',
        'status',
        'delivery_status',
        'sent_at',
        'delivered_at',
        'failed_at',
        'cost',
        'segments',
        'error_code',
        'error_message',
        'retry_count',
        'max_retries',
        'ip_address',
        'user_agent',
        'source',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'metadata' => 'array',
            'cost' => 'decimal:4',
            'segments' => 'integer',
            'retry_count' => 'integer',
            'max_retries' => 'integer',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * @return BelongsTo<SmsProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'sms_provider_id');
    }

    /**
     * @return BelongsTo<SmsTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Get logs by status.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get pending logs.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->status(self::STATUS_PENDING);
    }

    /**
     * Get delivered logs.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->status(self::STATUS_DELIVERED);
    }

    /**
     * Get failed logs.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->status(self::STATUS_FAILED);
    }

    /**
     * Get logs by message type.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('message_type', $type);
    }

    /**
     * Get OTP logs.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeOtp(Builder $query): Builder
    {
        return $query->type(self::TYPE_OTP);
    }

    /**
     * Get logs that can be retried.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeRetryable(Builder $query): Builder
    {
        return $query->status(self::STATUS_FAILED)
            ->whereColumn('retry_count', '<', 'max_retries');
    }

    /**
     * Get logs within date range.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeDateRange(Builder $query, \DateTimeInterface $from, \DateTimeInterface $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Get today's logs.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get this month's logs.
     *
     * @param  Builder<SmsLog>  $query
     * @return Builder<SmsLog>
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Mark as sent.
     */
    public function markAsSent(?string $requestId = null, ?string $messageId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'request_id' => $requestId ?? $this->request_id,
            'message_id' => $messageId ?? $this->message_id,
        ]);
    }

    /**
     * Mark as delivered.
     */
    public function markAsDelivered(?string $deliveryStatus = null): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivery_status' => $deliveryStatus,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $errorMessage, ?string $errorCode = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'failed_at' => now(),
        ]);
    }

    /**
     * Check if can retry.
     */
    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED
            && $this->retry_count < $this->max_retries;
    }

    /**
     * Increment retry count.
     */
    public function incrementRetry(): void
    {
        $this->increment('retry_count');
        $this->update(['status' => self::STATUS_PENDING]);
    }

    /**
     * Check if delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_QUEUED, self::STATUS_SENT], true);
    }

    /**
     * Get formatted cost.
     */
    public function getFormattedCost(): string
    {
        return '₹'.number_format($this->cost, 2);
    }

    /**
     * Get analytics summary for a period.
     *
     * @return array<string, mixed>
     */
    public static function getAnalytics(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $query = static::query()->dateRange($from, $to);

        $total = (clone $query)->count();
        $delivered = (clone $query)->delivered()->count();
        $failed = (clone $query)->failed()->count();
        $totalCost = (clone $query)->sum('cost');

        $byType = (clone $query)->selectRaw('message_type, COUNT(*) as count')
            ->groupBy('message_type')
            ->pluck('count', 'message_type')
            ->toArray();

        $byProvider = (clone $query)->selectRaw('provider_slug, COUNT(*) as count')
            ->groupBy('provider_slug')
            ->pluck('count', 'provider_slug')
            ->toArray();

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'totals' => [
                'sent' => $total,
                'delivered' => $delivered,
                'failed' => $failed,
                'pending' => $total - $delivered - $failed,
            ],
            'rates' => [
                'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
                'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
            ],
            'cost' => [
                'total' => round((float) $totalCost, 2),
                'average' => $total > 0 ? round((float) $totalCost / $total, 4) : 0,
            ],
            'by_type' => $byType,
            'by_provider' => $byProvider,
        ];
    }
}

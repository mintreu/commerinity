<?php

declare(strict_types=1);

namespace App\Models\Mlm;

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * MlmCommission Model - Commission Ledger
 *
 * Tracks all MLM commissions with full audit trail:
 * - Sponsor bonuses
 * - Level commissions (depth 1-4)
 * - Matching bonuses
 * - Level achievement bonuses
 * - Pool distributions
 */
class MlmCommission extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory(): \Database\Factories\MlmCommissionFactory
    {
        return \Database\Factories\MlmCommissionFactory::new();
    }

    protected $table = 'mlm_commissions';

    protected $fillable = [
        'uuid',
        'user_id',
        'genealogy_id',
        'from_user_id',
        'commissionable_type',
        'commissionable_id',
        'type',
        'level',
        'rate_percent',
        'base_amount',
        'gross_amount',
        'tds_amount',
        'admin_fee',
        'net_amount',
        'status',
        'paid_via_transaction_id',
        'paid_at',
        'commission_date',
        'period_key',
        'description',
        'metadata',
        'approved_by',
        'approved_at',
        'reversed_commission_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommissionTypeCast::class,
            'level' => 'integer',
            'rate_percent' => 'decimal:2',
            'base_amount' => 'integer',
            'gross_amount' => 'integer',
            'tds_amount' => 'integer',
            'admin_fee' => 'integer',
            'net_amount' => 'integer',
            'status' => CommissionStatusCast::class,
            'paid_at' => 'datetime',
            'commission_date' => 'date',
            'metadata' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MlmCommission $commission) {
            if (! $commission->uuid) {
                $commission->uuid = 'COM-'.Str::upper(Str::random(12));
            }

            if (! $commission->commission_date) {
                $commission->commission_date = now()->toDateString();
            }

            if (! $commission->period_key) {
                $commission->period_key = now()->format('Y-m');
            }

            // Calculate net amount if not set
            if (! $commission->net_amount) {
                $commission->calculateNetAmount();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the user who receives this commission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the genealogy record
     */
    public function genealogy(): BelongsTo
    {
        return $this->belongsTo(MlmGenealogy::class, 'genealogy_id');
    }

    /**
     * Get the user whose action triggered this commission
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the commissionable model (UserSubscription, Order, etc.)
     */
    public function commissionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the transaction when commission was paid
     */
    public function paidViaTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'paid_via_transaction_id');
    }

    /**
     * Get the admin who approved this commission
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the original commission (if this is a reversal)
     */
    public function reversedCommission(): BelongsTo
    {
        return $this->belongsTo(MlmCommission::class, 'reversed_commission_id');
    }

    /**
     * Get reversal commission (if this was reversed)
     */
    public function reversal(): BelongsTo
    {
        return $this->belongsTo(MlmCommission::class, 'reversed_commission_id');
    }

    // ========================================
    // Amount Calculations
    // ========================================

    /**
     * Calculate net amount after deductions
     */
    public function calculateNetAmount(): void
    {
        $this->net_amount = $this->gross_amount - $this->tds_amount - $this->admin_fee;
    }

    /**
     * Calculate TDS (10% if monthly total exceeds threshold)
     */
    public function calculateTds(int $threshold = 500000, float $rate = 10): void
    {
        // Get user's total commission this month
        $monthlyTotal = static::where('user_id', $this->user_id)
            ->where('period_key', $this->period_key)
            ->where('status', '!=', CommissionStatusCast::CANCELLED)
            ->where('status', '!=', CommissionStatusCast::REVERSED)
            ->sum('gross_amount');

        if (($monthlyTotal + $this->gross_amount) > $threshold) {
            $this->tds_amount = (int) round($this->gross_amount * ($rate / 100));
        } else {
            $this->tds_amount = 0;
        }

        $this->calculateNetAmount();
    }

    /**
     * Get gross amount in rupees
     */
    public function getGrossAmountInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->gross_amount);
    }

    /**
     * Get net amount in rupees
     */
    public function getNetAmountInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->net_amount);
    }

    /**
     * Get formatted gross amount
     */
    public function getFormattedGrossAmountAttribute(): string
    {
        return MoneyService::format($this->gross_amount);
    }

    /**
     * Get formatted net amount
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return MoneyService::format($this->net_amount);
    }

    // ========================================
    // Status Methods
    // ========================================

    /**
     * Approve this commission
     */
    public function approve(int $approvedById): void
    {
        if (! $this->status->canBeApproved()) {
            throw new \RuntimeException('Commission cannot be approved in current status');
        }

        $this->status = CommissionStatusCast::APPROVED;
        $this->approved_by = $approvedById;
        $this->approved_at = now();
        $this->save();
    }

    /**
     * Mark as processing
     */
    public function markProcessing(): void
    {
        $this->status = CommissionStatusCast::PROCESSING;
        $this->save();
    }

    /**
     * Mark as paid
     */
    public function markPaid(int $transactionId): void
    {
        $this->status = CommissionStatusCast::PAID;
        $this->paid_via_transaction_id = $transactionId;
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Put on hold
     */
    public function hold(?string $reason = null): void
    {
        if (! $this->status->canBeHeld()) {
            throw new \RuntimeException('Commission cannot be held in current status');
        }

        $this->status = CommissionStatusCast::HELD;
        if ($reason) {
            $this->description = ($this->description ?? '')."\nHeld: {$reason}";
        }
        $this->save();
    }

    /**
     * Cancel this commission
     */
    public function cancel(?string $reason = null): void
    {
        if (! $this->status->canBeCancelled()) {
            throw new \RuntimeException('Commission cannot be cancelled in current status');
        }

        $this->status = CommissionStatusCast::CANCELLED;
        if ($reason) {
            $this->description = ($this->description ?? '')."\nCancelled: {$reason}";
        }
        $this->save();
    }

    /**
     * Reverse this commission (create reversal entry)
     *
     * Reversal records use POSITIVE amounts (same as original).
     * The 'reversal' type indicates this is a clawback/deduction.
     * Link via reversed_commission_id for audit trail.
     */
    public function reverse(?string $reason = null): self
    {
        if (! $this->status->canBeReversed()) {
            throw new \RuntimeException('Commission cannot be reversed in current status');
        }

        // Create reversal commission with POSITIVE amounts
        // The 'reversal' type indicates this is money being clawed back
        $reversal = static::create([
            'user_id' => $this->user_id,
            'genealogy_id' => $this->genealogy_id,
            'from_user_id' => $this->from_user_id,
            'commissionable_type' => $this->commissionable_type,
            'commissionable_id' => $this->commissionable_id,
            'type' => CommissionTypeCast::REVERSAL,
            'level' => $this->level,
            'rate_percent' => $this->rate_percent,
            'base_amount' => $this->base_amount,
            'gross_amount' => $this->gross_amount,  // Positive - amount being reversed
            'tds_amount' => $this->tds_amount,      // Positive - TDS being refunded
            'admin_fee' => $this->admin_fee,        // Positive - fee being refunded
            'net_amount' => $this->net_amount,      // Positive - net being clawed back
            'status' => CommissionStatusCast::APPROVED,
            'commission_date' => now()->toDateString(),
            'description' => "Reversal of {$this->uuid}".($reason ? ": {$reason}" : ''),
            'reversed_commission_id' => $this->id,
        ]);

        // Update original commission status
        $this->status = CommissionStatusCast::REVERSED;
        $this->save();

        return $reversal;
    }

    // ========================================
    // Status Checks
    // ========================================

    public function isPending(): bool
    {
        return $this->status->getValue() === CommissionStatusCast::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status->getValue() === CommissionStatusCast::APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status->getValue() === CommissionStatusCast::PAID;
    }

    public function isHeld(): bool
    {
        return $this->status->getValue() === CommissionStatusCast::HELD;
    }

    public function isCancelled(): bool
    {
        return $this->status->getValue() === CommissionStatusCast::CANCELLED;
    }

    public function isReversed(): bool
    {
        return $this->status->getValue() === CommissionStatusCast::REVERSED;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('status', CommissionStatusCast::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', CommissionStatusCast::APPROVED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', CommissionStatusCast::PAID);
    }

    public function scopeForPeriod($query, string $periodKey)
    {
        return $query->where('period_key', $periodKey);
    }

    public function scopeThisMonth($query)
    {
        return $query->where('period_key', now()->format('Y-m'));
    }

    public function scopePayable($query)
    {
        return $query->whereIn('status', [
            CommissionStatusCast::PENDING,
            CommissionStatusCast::APPROVED,
        ]);
    }

    // ========================================
    // Aggregates
    // ========================================

    /**
     * Get total earnings for a user in a period
     */
    public static function getTotalEarnings(int $userId, ?string $periodKey = null): int
    {
        $query = static::where('user_id', $userId)
            ->where('status', CommissionStatusCast::PAID);

        if ($periodKey) {
            $query->where('period_key', $periodKey);
        }

        return (int) $query->sum('net_amount');
    }

    /**
     * Get pending earnings for a user
     */
    public static function getPendingEarnings(int $userId): int
    {
        return (int) static::where('user_id', $userId)
            ->whereIn('status', [CommissionStatusCast::PENDING, CommissionStatusCast::APPROVED])
            ->sum('net_amount');
    }

    // ========================================
    // Factory Methods
    // ========================================

    /**
     * Create a sponsor bonus commission
     */
    public static function createSponsorBonus(
        int $userId,
        int $fromUserId,
        int $amount,
        Model $commissionable,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'from_user_id' => $fromUserId,
            'commissionable_type' => get_class($commissionable),
            'commissionable_id' => $commissionable->getKey(),
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'gross_amount' => $amount,
            'net_amount' => $amount,
            'description' => 'Direct sponsor bonus',
        ]);
    }

    /**
     * Create a level commission
     */
    public static function createLevelCommission(
        int $userId,
        int $fromUserId,
        int $level,
        float $ratePercent,
        int $baseAmount,
        int $commissionAmount,
        Model $commissionable,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'from_user_id' => $fromUserId,
            'commissionable_type' => get_class($commissionable),
            'commissionable_id' => $commissionable->getKey(),
            'type' => CommissionTypeCast::LEVEL_COMMISSION,
            'level' => $level,
            'rate_percent' => $ratePercent,
            'base_amount' => $baseAmount,
            'gross_amount' => $commissionAmount,
            'net_amount' => $commissionAmount,
            'description' => "Level {$level} commission ({$ratePercent}%)",
        ]);
    }

    /**
     * Create a level achievement bonus
     */
    public static function createLevelAchievementBonus(
        int $userId,
        int $levelId,
        int $amount,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'type' => CommissionTypeCast::LEVEL_ACHIEVEMENT,
            'gross_amount' => $amount,
            'net_amount' => $amount,
            'description' => 'Level achievement bonus',
            'metadata' => ['level_id' => $levelId],
        ]);
    }

    // ========================================
    // Originator Commission Factory Methods
    // ========================================

    /**
     * Create originator joining commission (when originated user subscribes)
     */
    public static function createOriginatorJoining(
        int $originatorId,
        int $fromUserId,
        int $baseAmount,
        float $ratePercent,
        int $commissionAmount,
        Model $commissionable,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $originatorId,
            'genealogy_id' => $genealogyId,
            'from_user_id' => $fromUserId,
            'commissionable_type' => get_class($commissionable),
            'commissionable_id' => $commissionable->getKey(),
            'type' => CommissionTypeCast::ORIGINATOR_JOINING,
            'rate_percent' => $ratePercent,
            'base_amount' => $baseAmount,
            'gross_amount' => $commissionAmount,
            'net_amount' => $commissionAmount,
            'description' => "Originator commission ({$ratePercent}%)",
        ]);
    }

    /**
     * Create originator recurring commission (on withdrawal/monthly)
     */
    public static function createOriginatorRecurring(
        int $originatorId,
        int $fromUserId,
        int $baseAmount,
        float $ratePercent,
        int $commissionAmount,
        ?Model $commissionable = null,
        ?int $genealogyId = null,
        string $frequency = 'on_withdrawal'
    ): self {
        $data = [
            'user_id' => $originatorId,
            'genealogy_id' => $genealogyId,
            'from_user_id' => $fromUserId,
            'type' => CommissionTypeCast::ORIGINATOR_RECURRING,
            'rate_percent' => $ratePercent,
            'base_amount' => $baseAmount,
            'gross_amount' => $commissionAmount,
            'net_amount' => $commissionAmount,
            'description' => "Recurring originator commission ({$ratePercent}%)",
            'metadata' => ['frequency' => $frequency],
        ];

        if ($commissionable) {
            $data['commissionable_type'] = get_class($commissionable);
            $data['commissionable_id'] = $commissionable->getKey();
        }

        return static::create($data);
    }

    /**
     * Create agent salary payout
     */
    public static function createAgentSalary(
        int $agentId,
        int $salaryAmount,
        string $tierName,
        array $metrics,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $agentId,
            'genealogy_id' => $genealogyId,
            'type' => CommissionTypeCast::AGENT_SALARY,
            'gross_amount' => $salaryAmount,
            'net_amount' => $salaryAmount,
            'description' => "Monthly salary - {$tierName}",
            'metadata' => [
                'tier' => $tierName,
                'metrics' => $metrics,
            ],
        ]);
    }

    /**
     * Create income deduction record (from member earnings)
     */
    public static function createIncomeDeduction(
        int $memberId,
        int $deductionAmount,
        int $baseAmount,
        float $ratePercent,
        Model $sourceCommission,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $memberId,
            'genealogy_id' => $genealogyId,
            'commissionable_type' => get_class($sourceCommission),
            'commissionable_id' => $sourceCommission->getKey(),
            'type' => CommissionTypeCast::INCOME_DEDUCTION,
            'rate_percent' => $ratePercent,
            'base_amount' => $baseAmount,
            'gross_amount' => $deductionAmount,
            'net_amount' => $deductionAmount,
            'description' => config('mlm.income_deduction.description', 'Platform Service Fee'),
        ]);
    }

    // ========================================
    // Task Commission Factory Methods
    // ========================================

    /**
     * Create task completion commission
     */
    public static function createTaskCompletion(
        int $userId,
        int $rewardAmount,
        string $taskName,
        ?int $taskId = null,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'type' => CommissionTypeCast::TASK_COMPLETION,
            'gross_amount' => $rewardAmount,
            'net_amount' => $rewardAmount,
            'description' => "Task completed: {$taskName}",
            'metadata' => ['task_id' => $taskId, 'task_name' => $taskName],
        ]);
    }

    /**
     * Create milestone bonus
     */
    public static function createMilestoneBonus(
        int $userId,
        int $bonusAmount,
        string $milestoneName,
        ?int $milestoneId = null,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'type' => CommissionTypeCast::MILESTONE_BONUS,
            'gross_amount' => $bonusAmount,
            'net_amount' => $bonusAmount,
            'description' => "Milestone achieved: {$milestoneName}",
            'metadata' => ['milestone_id' => $milestoneId, 'milestone_name' => $milestoneName],
        ]);
    }

    /**
     * Create referral bonus (non-MLM)
     */
    public static function createReferralBonus(
        int $userId,
        int $fromUserId,
        int $bonusAmount,
        ?Model $commissionable = null,
        ?int $genealogyId = null
    ): self {
        $data = [
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'from_user_id' => $fromUserId,
            'type' => CommissionTypeCast::REFERRAL_BONUS,
            'gross_amount' => $bonusAmount,
            'net_amount' => $bonusAmount,
            'description' => 'Referral bonus',
        ];

        if ($commissionable) {
            $data['commissionable_type'] = get_class($commissionable);
            $data['commissionable_id'] = $commissionable->getKey();
        }

        return static::create($data);
    }

    /**
     * Create performance bonus
     */
    public static function createPerformanceBonus(
        int $userId,
        int $bonusAmount,
        string $kpiName,
        array $metrics,
        ?int $genealogyId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'type' => CommissionTypeCast::PERFORMANCE_BONUS,
            'gross_amount' => $bonusAmount,
            'net_amount' => $bonusAmount,
            'description' => "Performance bonus: {$kpiName}",
            'metadata' => [
                'kpi_name' => $kpiName,
                'metrics' => $metrics,
            ],
        ]);
    }

    /**
     * Create custom commission (fully flexible)
     */
    public static function createCustom(
        int $userId,
        int $amount,
        string $description,
        array $metadata = [],
        ?int $fromUserId = null,
        ?Model $commissionable = null,
        ?int $genealogyId = null
    ): self {
        $data = [
            'user_id' => $userId,
            'genealogy_id' => $genealogyId,
            'from_user_id' => $fromUserId,
            'type' => CommissionTypeCast::CUSTOM,
            'gross_amount' => $amount,
            'net_amount' => $amount,
            'description' => $description,
            'metadata' => $metadata,
        ];

        if ($commissionable) {
            $data['commissionable_type'] = get_class($commissionable);
            $data['commissionable_id'] = $commissionable->getKey();
        }

        return static::create($data);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

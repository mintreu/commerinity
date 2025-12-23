<?php

declare(strict_types=1);

namespace App\Models\Recruitment;

use App\Casts\JobApplicationStatusCast;
use App\Models\Address;
use App\Models\Transaction;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JobApplication extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory(): \Database\Factories\JobApplicationFactory
    {
        return \Database\Factories\JobApplicationFactory::new();
    }

    protected $fillable = [
        'uuid',
        'recruitment_id',
        'applicant_type',
        'applicant_id',
        'guardian_name',
        'address_id',
        'educations',
        'skills',
        'experiences',
        'reference_name',
        'reference_contact',
        'is_paid',
        'amount',
        'transaction_id',
        'status',
        'status_feedback',
        'submitted_at',
        'import_batch_id',
        'import_data',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobApplicationStatusCast::class,
            'is_paid' => 'boolean',
            'amount' => 'integer',
            'submitted_at' => 'datetime',
            'educations' => 'array',
            'skills' => 'array',
            'experiences' => 'array',
            'import_data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobApplication $application) {
            if (! $application->uuid) {
                $application->uuid = 'APP-'.now()->format('ym').'-'.strtoupper(Str::random(8));
            }

            // Set amount from recruitment fees if not set
            if (! $application->amount && $application->recruitment_id) {
                $recruitment = Recruitment::find($application->recruitment_id);
                if ($recruitment && $recruitment->is_payable) {
                    $application->amount = $recruitment->fees;
                }
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function recruitment(): BelongsTo
    {
        return $this->belongsTo(Recruitment::class);
    }

    /**
     * Polymorphic relation to applicant (User or external candidate)
     */
    public function applicant(): MorphTo
    {
        return $this->morphTo();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ========================================
    // Accessors
    // ========================================

    public function getAmountFormattedAttribute(): string
    {
        return MoneyService::format($this->amount);
    }

    public function getAmountInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->amount);
    }

    public function getCanEditAttribute(): bool
    {
        return $this->status->canEdit();
    }

    public function getCanWithdrawAttribute(): bool
    {
        return $this->status->canWithdraw();
    }

    // ========================================
    // Status Methods
    // ========================================

    public function markAsPaid(int $transactionId): void
    {
        $this->update([
            'is_paid' => true,
            'transaction_id' => $transactionId,
            'status' => JobApplicationStatusCast::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function submit(): void
    {
        // For free applications, directly submit
        if (! $this->recruitment->is_payable) {
            $this->update([
                'status' => JobApplicationStatusCast::Submitted,
                'submitted_at' => now(),
            ]);
        } else {
            $this->update([
                'status' => JobApplicationStatusCast::AwaitingPayment,
            ]);
        }
    }

    public function startReview(): void
    {
        $this->update([
            'status' => JobApplicationStatusCast::UnderReview,
        ]);
    }

    public function accept(?string $feedback = null): void
    {
        $this->update([
            'status' => JobApplicationStatusCast::Accepted,
            'status_feedback' => $feedback,
        ]);
    }

    public function reject(?string $feedback = null): void
    {
        $this->update([
            'status' => JobApplicationStatusCast::Rejected,
            'status_feedback' => $feedback,
        ]);
    }

    public function withdraw(?string $reason = null): void
    {
        $this->update([
            'status' => JobApplicationStatusCast::Withdrawn,
            'status_feedback' => $reason,
        ]);
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeForRecruitment(Builder $query, int $recruitmentId): Builder
    {
        return $query->where('recruitment_id', $recruitmentId);
    }

    public function scopeForApplicant(Builder $query, Model $applicant): Builder
    {
        return $query->where('applicant_type', $applicant->getMorphClass())
            ->where('applicant_id', $applicant->getKey());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            JobApplicationStatusCast::Submitted,
            JobApplicationStatusCast::UnderReview,
        ]);
    }

    public function scopeAwaitingPayment(Builder $query): Builder
    {
        return $query->where('status', JobApplicationStatusCast::AwaitingPayment);
    }

    public function scopeFromImport(Builder $query, string $batchId): Builder
    {
        return $query->where('import_batch_id', $batchId);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'wallet_id',
        'transactionable_type',
        'transactionable_id',
        'type',
        'status',
        'amount',
        'fee',
        'tax',
        'net_amount',
        'currency',
        'payment_method',
        'integration_id',
        'provider_order_id',
        'provider_transaction_id',
        'provider_signature',
        'checkout_url',
        'qr_code_url',
        'is_verified',
        'verified_at',
        'description',
        'purpose',
        'notes',
        'reference_number',
        'parent_transaction_id',
        'expires_at',
        'balance_after',
        'metadata',
        'provider_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'fee' => 'integer',
            'tax' => 'integer',
            'net_amount' => 'integer',
            'balance_after' => 'integer',
            'type' => TransactionTypeCast::class,
            'status' => TransactionStatusCast::class,
            'payment_method' => PaymentMethodCast::class,
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'provider_response' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (! $transaction->uuid) {
                $transaction->uuid = 'TXN-'.Str::upper(Str::random(12));
            }

            // Calculate net amount if not set
            if (! $transaction->net_amount) {
                $transaction->net_amount = $transaction->amount - $transaction->fee - $transaction->tax;
            }

            // Generate reference number if not set
            if (! $transaction->reference_number) {
                $transaction->reference_number = 'REF-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the wallet this transaction belongs to
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the transactionable model (Order, Subscription, etc.)
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the integration/payment provider used
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    /**
     * Get the parent transaction (for refunds, chargebacks)
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Get child transactions (refunds of this transaction)
     */
    public function childTransactions(): HasOne
    {
        return $this->hasOne(Transaction::class, 'parent_transaction_id');
    }

    // ========================================
    // Status Methods
    // ========================================

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === TransactionStatusCast::COMPLETED;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatusCast::PENDING;
    }

    /**
     * Check if transaction is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Check if transaction has expired
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if transaction can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status->canBeRefunded()
            && $this->type === TransactionTypeCast::DEBIT;
    }

    /**
     * Mark transaction as verified
     */
    public function markAsVerified(): void
    {
        $this->is_verified = true;
        $this->verified_at = now();
        $this->save();
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = TransactionStatusCast::COMPLETED;
        $this->markAsVerified();
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(?string $reason = null): void
    {
        $this->status = TransactionStatusCast::FAILED;
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    // ========================================
    // Amount Methods (All in Paisa)
    // ========================================

    /**
     * Get amount in Rupees
     */
    public function getAmountInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->amount);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type->isPositive() ? '+' : '-';

        return $prefix.MoneyService::format($this->amount);
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatusCast::COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', TransactionStatusCast::PENDING);
    }

    public function scopeCredits($query)
    {
        return $query->where('type', TransactionTypeCast::CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('type', TransactionTypeCast::DEBIT);
    }

    public function scopeByPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeByPaymentMethod($query, PaymentMethodCast $method)
    {
        return $query->where('payment_method', $method);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

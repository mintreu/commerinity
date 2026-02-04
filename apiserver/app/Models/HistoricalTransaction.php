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

class HistoricalTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'historical_transactions';

    protected $fillable = [
        'source_transaction_id',
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
        'checkout_type',
        'integration_id',
        'provider_order_id',
        'provider_gen_id',
        'provider_gen_session',
        'provider_gen_link',
        'provider_gen_qr',
        'provider_transaction_id',
        'provider_signature',
        'provider_generated_sign',
        'qr_code_url',
        'success_url',
        'failure_url',
        'success_redirect_url',
        'failure_redirect_url',
        'verified',
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
        'archived_at',
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
            'verified' => 'boolean',
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'provider_response' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HistoricalTransaction $transaction) {
            if (! $transaction->uuid) {
                $transaction->uuid = 'TXN-'.Str::upper(Str::random(12));
            }

            if (! $transaction->net_amount) {
                $transaction->net_amount = $transaction->amount - $transaction->fee - $transaction->tax;
            }

            if (! $transaction->reference_number) {
                $transaction->reference_number = 'REF-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id');
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(HistoricalTransaction::class, 'parent_transaction_id');
    }

    public function childTransactions(): HasOne
    {
        return $this->hasOne(HistoricalTransaction::class, 'parent_transaction_id');
    }

    // ========================================
    // Amount Methods (All in Paisa)
    // ========================================

    public function getAmountInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->amount);
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type->isPositive() ? '+' : '-';

        return $prefix.MoneyService::format($this->amount);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

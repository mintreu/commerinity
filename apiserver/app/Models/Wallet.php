<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\WalletStatusCast;
use App\Services\MoneyService;
use App\Traits\HasTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory;
    use HasTransaction; // ⭐ Makes wallet payable for topup
    use SoftDeletes;

    // Define amount column for transactions
    public const TRANSACTION_AMOUNT_COLUMN = 'balance';

    protected $fillable = [
        'uuid',
        'walletable_type',
        'walletable_id',
        'balance',
        'hold_balance',
        'total_credited',
        'total_debited',
        'points',
        'pin',
        'pin_updated_at',
        'currency',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'hold_balance' => 'integer',
            'total_credited' => 'integer',
            'total_debited' => 'integer',
            'points' => 'integer',
            'pin_updated_at' => 'datetime',
            'status' => WalletStatusCast::class,
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Wallet $wallet) {
            if (! $wallet->uuid) {
                $wallet->uuid = 'WAL-'.Str::upper(Str::random(12));
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the owner of the wallet (User, Merchant, etc.)
     */
    public function walletable(): MorphTo
    {
        return $this->morphTo();
    }

    // alish use for checkout
    public function customer()
    {
        return $this->walletable();
    }

    /**
     * Get all transactions for this wallet
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all historical transactions for this wallet
     */
    public function historicalTransactions(): HasMany
    {
        return $this->hasMany(HistoricalTransaction::class);
    }

    /**
     * Get all beneficiary accounts
     */
    public function beneficiaryAccounts(): HasMany
    {
        return $this->hasMany(BeneficiaryAccount::class);
    }

    /**
     * Get the default beneficiary account
     */
    public function defaultBeneficiary(): HasOne
    {
        return $this->hasOne(BeneficiaryAccount::class)->where('is_default', true);
    }

    // ========================================
    // PIN Methods
    // ========================================

    /**
     * Set the wallet PIN (will be hashed)
     */
    public function setPin(string $pin): void
    {
        $this->pin = Hash::make($pin);
        $this->pin_updated_at = now();
        $this->save();
    }

    /**
     * Verify the wallet PIN
     */
    public function verifyPin(string $pin): bool
    {
        if (! $this->pin) {
            return false;
        }

        return Hash::check($pin, $this->pin);
    }

    /**
     * Check if wallet has PIN set
     */
    public function hasPin(): bool
    {
        return ! empty($this->pin);
    }

    // ========================================
    // Balance Methods (All in Paisa)
    // ========================================

    /**
     * Get available balance (balance - hold_balance)
     */
    public function getAvailableBalanceAttribute(): int
    {
        return max(0, $this->balance - $this->hold_balance);
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance(int $amountInPaisa): bool
    {
        return $this->available_balance >= $amountInPaisa;
    }

    /**
     * Get balance in Rupees (for display)
     */
    public function getBalanceInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->balance);
    }

    /**
     * Get formatted balance with currency symbol
     */
    public function getFormattedBalanceAttribute(): string
    {
        return MoneyService::format($this->balance);
    }

    // ========================================
    // Status Methods
    // ========================================

    /**
     * Check if wallet can perform transactions
     */
    public function canTransact(): bool
    {
        return $this->status->canTransact();
    }

    /**
     * Check if wallet can receive funds
     */
    public function canReceive(): bool
    {
        return $this->status->canReceive();
    }

    /**
     * Suspend the wallet
     */
    public function suspend(): void
    {
        $this->status = WalletStatusCast::SUSPENDED;
        $this->save();
    }

    /**
     * Activate the wallet
     */
    public function activate(): void
    {
        $this->status = WalletStatusCast::ACTIVE;
        $this->save();
    }

    // ========================================
    // Withdrawal Configuration
    // ========================================

    /**
     * Get withdrawal threshold enabled status
     */
    public function isWithdrawalThresholdEnabled(): bool
    {
        return $this->metadata['withdrawal']['threshold_enabled'] ?? true;
    }

    /**
     * Get minimum withdrawal amount in paisa
     */
    public function getMinimumWithdrawalAmount(): int
    {
        return $this->metadata['withdrawal']['minimum_amount'] ?? 10000; // Default: ₹100
    }

    /**
     * Set withdrawal configuration
     */
    public function setWithdrawalConfig(bool $thresholdEnabled, int $minimumAmount): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['withdrawal'] = [
            'threshold_enabled' => $thresholdEnabled,
            'minimum_amount' => $minimumAmount,
        ];
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Check if amount meets minimum withdrawal threshold
     */
    public function meetsWithdrawalThreshold(int $amountInPaisa): bool
    {
        if (! $this->isWithdrawalThresholdEnabled()) {
            return true;
        }

        return $amountInPaisa >= $this->getMinimumWithdrawalAmount();
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('status', WalletStatusCast::ACTIVE);
    }

    public function scopeByOwner($query, Model $owner)
    {
        return $query->where('walletable_type', $owner->getMorphClass())
            ->where('walletable_id', $owner->getKey());
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

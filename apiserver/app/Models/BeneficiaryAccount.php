<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\BeneficiaryTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BeneficiaryAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'accountable_type',
        'accountable_id',
        'wallet_id',
        'type',
        'account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
        'upi_id',
        'holder_name',
        'integration_id',
        'provider_beneficiary_id',
        'status',
        'rejection_reason',
        'verified_at',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => BeneficiaryTypeCast::class,
            'status' => BeneficiaryStatusCast::class,
            'is_default' => 'boolean',
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BeneficiaryAccount $account) {
            if (! $account->uuid) {
                $account->uuid = Str::upper(Str::random(8));
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the owner (User, Merchant, etc.)
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the associated wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the payment integration used for verification
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Check if this is a bank account
     */
    public function isBank(): bool
    {
        return $this->type->isBank();
    }

    /**
     * Check if this is a UPI account
     */
    public function isUpi(): bool
    {
        return $this->type->isUpi();
    }

    /**
     * Check if account can receive payouts
     */
    public function canReceivePayout(): bool
    {
        return $this->status->canReceivePayout();
    }

    /**
     * Check if account is verified
     */
    public function isVerified(): bool
    {
        return $this->status === BeneficiaryStatusCast::VERIFIED;
    }

    /**
     * Make this account the default
     */
    public function makeDefault(): void
    {
        // Remove default from other accounts of same owner
        static::where('accountable_type', $this->accountable_type)
            ->where('accountable_id', $this->accountable_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->is_default = true;
        $this->save();
    }

    /**
     * Get masked account number for display
     */
    public function getMaskedAccountNumberAttribute(): ?string
    {
        if (! $this->account_number) {
            return null;
        }

        $length = strlen($this->account_number);
        if ($length <= 4) {
            return $this->account_number;
        }

        return str_repeat('*', $length - 4).substr($this->account_number, -4);
    }

    /**
     * Get display name for the account
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isUpi()) {
            return $this->upi_id ?? 'UPI Account';
        }

        return ($this->bank_name ?? 'Bank').' - '.$this->masked_account_number;
    }

    /**
     * Get masked account display for payouts (used in transaction descriptions)
     */
    public function getMaskedAccountDisplay(): string
    {
        if ($this->isUpi()) {
            return 'UPI ('.$this->upi_id.')';
        }

        return ($this->bank_name ?? 'Bank').' '.$this->masked_account_number;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeVerified($query)
    {
        return $query->where('status', BeneficiaryStatusCast::VERIFIED);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeBank($query)
    {
        return $query->whereIn('type', [BeneficiaryTypeCast::SAVINGS, BeneficiaryTypeCast::CURRENT]);
    }

    public function scopeUpi($query)
    {
        return $query->where('type', BeneficiaryTypeCast::UPI);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

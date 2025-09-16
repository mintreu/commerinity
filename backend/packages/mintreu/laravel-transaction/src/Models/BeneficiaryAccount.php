<?php

namespace Mintreu\LaravelTransaction\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountStatusCast;
use Mintreu\LaravelTransaction\Casts\BeneficiaryAccountTypeCast;
use Mintreu\Toolkit\Traits\HasUnique;

class BeneficiaryAccount extends Model
{
    use HasUnique;

    protected $table = 'beneficiary_accounts';

    protected $fillable = [
        'uuid',
        'upi_handle',
        'ifsc',
        'bank_name',
        'bank_branch',
        'account_name',
        'account_number',
        'type',
        'default',
        'status',
        'status_feedback',
        'integration_id',
        'source_fund_account',
        'source_upi_handle',
        'provider_beneficiary_id',
        'provider_beneficiary_type',
        'provider_upi_handle',
        'beneficiary_active',
        'provider_data',
        'wallet_id',
        'extra',
    ];

    protected $casts = [
        'status'            => BeneficiaryAccountStatusCast::class,
        'type'      => BeneficiaryAccountTypeCast::class,
        'beneficiary_active'=> 'boolean',
        'default'           => 'boolean',
        'provider_data'     => 'array',
        'extra'             => 'array',
    ];


    protected static function booted()
    {
        static::creating(function ($record){
            $record->setUniqueCode('uuid',8);
        });
        parent::booted();
    }


    /**
     * Polymorphic owner (User, Merchant, Wallet, etc.)
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Linked Provider
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id');
    }

    /**
     * Linked Wallet (optional)
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Quick check if beneficiary is bank type
     */
    public function isBank(): bool
    {
        return in_array($this->account_type, [
            BeneficiaryAccountTypeCast::SAVINGS,
            BeneficiaryAccountTypeCast::CURRENT,
        ]);
    }

    /**
     * Quick check if beneficiary is UPI type
     */
    public function isUpi(): bool
    {
        return $this->account_type === BeneficiaryAccountTypeCast::UPI;
    }

    /**
     * Mark this account as default for owner
     */
    public function makeDefault(): void
    {
        static::where('accountable_type', $this->accountable_type)
            ->where('accountable_id', $this->accountable_id)
            ->update(['default' => false]);

        $this->update(['default' => true]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\BeneficiaryAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * HasBeneficiary Trait
 *
 * Add to any model (User, Wallet) to enable beneficiary account management.
 * Supports bank accounts and UPI accounts for withdrawals/p2p transfers.
 *
 * Usage:
 *   use HasBeneficiary;
 *   // Model now has beneficiaryAccounts() relationship
 */
trait HasBeneficiary
{
    /**
     * Get all beneficiary accounts for this model
     */
    public function beneficiaryAccounts(): MorphMany
    {
        return $this->morphMany(BeneficiaryAccount::class, 'accountable');
    }

    /**
     * Get default beneficiary account
     */
    public function defaultBeneficiary(): ?BeneficiaryAccount
    {
        return $this->beneficiaryAccounts()->where('is_default', true)->first();
    }

    /**
     * Add a new beneficiary account
     */
    public function addBeneficiary(array $attributes): BeneficiaryAccount
    {
        return $this->beneficiaryAccounts()->create($attributes);
    }

    /**
     * Set default beneficiary account
     */
    public function setDefaultBeneficiary(BeneficiaryAccount $account): void
    {
        // Remove default from other accounts
        $this->beneficiaryAccounts()->where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $account->update(['is_default' => true]);
    }

    /**
     * Check if model has any beneficiary
     */
    public function hasBeneficiaries(): bool
    {
        return $this->beneficiaryAccounts()->exists();
    }

    /**
     * Get active beneficiaries only
     */
    public function activeBeneficiaries(): MorphMany
    {
        return $this->beneficiaryAccounts()->where('status', true);
    }
}

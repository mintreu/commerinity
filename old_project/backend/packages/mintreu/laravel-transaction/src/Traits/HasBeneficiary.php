<?php

namespace Mintreu\LaravelTransaction\Traits;


use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mintreu\LaravelTransaction\Models\BeneficiaryAccount;

trait HasBeneficiary
{
    /**
     * Get all beneficiary accounts (bank / upi) for this model
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
        return $this->beneficiaryAccounts()->where('default', true)->first();
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
        $account->makeDefault();
    }
}

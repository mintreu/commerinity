<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BeneficiaryAccount;
use App\Models\Wallet;
use App\Services\Payment\PayoutService;
use Illuminate\Support\Facades\Log;

final class BeneficiaryAccountService
{
    protected BeneficiaryAccount $beneficiaryAccount;

    protected Wallet $wallet;

    public function __construct(BeneficiaryAccount $beneficiaryAccount)
    {
        $this->beneficiaryAccount = $beneficiaryAccount;
        $this->beneficiaryAccount->load('wallet');
        $this->wallet = $this->beneficiaryAccount->wallet;
    }

    public static function make(BeneficiaryAccount $beneficiaryAccount): self
    {
        return new self($beneficiaryAccount);
    }



    public function sync(): bool
    {
        $payoutService = app(PayoutService::class);

        // Register with payout provider (Cashfree/Razorpay/Native)
        // Used after creating beneficiary account or modifying it
        $result = $payoutService->createBeneficiary($this->beneficiaryAccount);

        if (! $result['success']) {
            Log::error('Beneficiary sync failed', [
                'beneficiary_id' => $this->beneficiaryAccount->id,
                'error' => $result['message'] ?? 'Unknown error',
            ]);

            return false;
        }

        // Provider already updated the beneficiary record in their createBeneficiary method
        // (status, provider_beneficiary_id, metadata are set by the provider)

        Log::info('Beneficiary synced successfully', [
            'beneficiary_id' => $this->beneficiaryAccount->id,
            'provider_beneficiary_id' => $this->beneficiaryAccount->fresh()->provider_beneficiary_id,
        ]);

        return $this->beneficiaryAccount->fresh()->isVerified();
    }



}

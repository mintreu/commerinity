<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payout\Contracts;

use App\Models\BeneficiaryAccount;
use App\Models\Integration;
use App\Services\IntegrationServices\Payout\DTOs\PayoutRequest;
use App\Services\IntegrationServices\Payout\DTOs\PayoutResponse;

/**
 * PayoutProviderInterface - Contract for payout/withdrawal providers
 *
 * Implementations:
 * - NativePayoutProvider (manual bank transfer tracking)
 * - CashfreePayoutProvider (API-based payouts)
 * - RazorpayPayoutProvider (API-based payouts)
 */
interface PayoutProviderInterface
{
    /**
     * Get the provider slug/identifier
     */
    public function getSlug(): string;

    /**
     * Get human-readable provider name
     */
    public function getName(): string;

    /**
     * Check if the provider is properly configured and available
     */
    public function isAvailable(): bool;

    /**
     * Initiate a payout to beneficiary account
     */
    public function initiate(PayoutRequest $request): PayoutResponse;

    /**
     * Check payout status
     */
    public function checkStatus(string $payoutId): PayoutResponse;

    /**
     * Get supported payout methods
     *
     * @return array<string>
     */
    public function getSupportedMethods(): array;

    // ========================================
    // Beneficiary Operations
    // ========================================

    /**
     * Create beneficiary account with provider
     *
     * @param  BeneficiaryAccount $beneficiary  The beneficiary account to register
     * @param  ?Integration  $integration  Optional integration override
     * @return array{success: bool, beneficiary_id?: string, message?: string}
     */
    public function createBeneficiary(BeneficiaryAccount $beneficiary, ?Integration $integration = null): array;

    /**
     * Update beneficiary account with provider
     *
     * @param  array<string, mixed>  $data  Updated details
     * @return array{success: bool, message?: string}
     */
    public function updateBeneficiary(BeneficiaryAccount $beneficiary, array $data): array;

    /**
     * Delete beneficiary account from provider
     *
     * @return array{success: bool, message?: string}
     */
    public function deleteBeneficiary(BeneficiaryAccount $beneficiary): array;

    /**
     * Get beneficiary details from provider
     *
     * @return array{success: bool, data?: array, message?: string}
     */
    public function getBeneficiary(BeneficiaryAccount $beneficiary): array;
}

<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;

/**
 * PayoutProviderInterface - Contract for payout/withdrawal providers
 *
 * Implementations:
 * - NativePayoutProvider (manual bank transfer tracking)
 * - CashfreePayoutProvider (future - API-based payouts)
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
     *
     * @param  PayoutRequest  $request  Payout details
     * @return PayoutResponse Response with payout status
     */
    public function initiate(PayoutRequest $request): PayoutResponse;

    /**
     * Check payout status
     *
     * @param  string  $payoutId  Provider's payout ID
     * @return PayoutResponse Response with current status
     */
    public function checkStatus(string $payoutId): PayoutResponse;

    /**
     * Get supported payout methods
     *
     * @return array<string> List of supported payout method slugs
     */
    public function getSupportedMethods(): array;
}

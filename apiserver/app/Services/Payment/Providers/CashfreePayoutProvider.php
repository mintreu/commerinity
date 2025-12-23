<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Models\BeneficiaryAccount;
use App\Models\Integration;
use App\Services\Payment\Contracts\PayoutProviderInterface;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CashfreePayoutProvider - Cashfree Payouts API Integration
 *
 * Default payout provider for India.
 * Supports: Bank Transfer (IMPS/NEFT/RTGS), UPI
 *
 * @see https://docs.cashfree.com/reference/payout-apis
 */
final class CashfreePayoutProvider implements PayoutProviderInterface
{
    private const SANDBOX_URL = 'https://payout-gamma.cashfree.com/payout/v1';

    private const PRODUCTION_URL = 'https://payout-api.cashfree.com/payout/v1';

    private const TOKEN_CACHE_KEY = 'cashfree_payout_token';

    private const TOKEN_CACHE_TTL = 300; // 5 minutes (token valid for 5 mins)

    private ?Integration $integration = null;

    public function getSlug(): string
    {
        return 'cashfree';
    }

    public function getName(): string
    {
        return 'Cashfree Payouts';
    }

    public function isAvailable(): bool
    {
        $integration = $this->getIntegration();

        return $integration !== null && $integration->isUsable();
    }

    public function getSupportedMethods(): array
    {
        return ['bank_transfer', 'upi'];
    }

    /**
     * Initiate a payout transfer
     */
    public function initiate(PayoutRequest $request): PayoutResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PayoutResponse::failed('Cashfree Payouts not configured');
        }

        // Step 1: Load beneficiary account
        $beneficiary = BeneficiaryAccount::find($request->beneficiaryAccountId);
        if (! $beneficiary) {
            return PayoutResponse::failed('Beneficiary account not found');
        }

        if (! $beneficiary->canReceivePayout()) {
            return PayoutResponse::failed('Beneficiary account is not verified for payouts');
        }

        // Step 2: Ensure beneficiary is registered with Cashfree
        if (! $beneficiary->provider_beneficiary_id) {
            $addResult = $this->addBeneficiary($beneficiary, $integration);
            if (! $addResult['success']) {
                return PayoutResponse::failed($addResult['message']);
            }
            $beneficiary->update(['provider_beneficiary_id' => $addResult['bene_id']]);
        }

        // Step 3: Request transfer
        try {
            $transferMode = $beneficiary->isUpi() ? 'upi' : 'banktransfer';

            $response = Http::withHeaders($this->getAuthHeaders($integration))
                ->timeout(30)
                ->post($this->getBaseUrl($integration).'/requestTransfer', [
                    'beneId' => $beneficiary->provider_beneficiary_id,
                    'amount' => $request->getAmountInRupees(),
                    'transferId' => $request->transactionId,
                    'transferMode' => $transferMode,
                    'remarks' => $request->description ?? $request->purpose ?? 'Payout',
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'SUCCESS') {
                Log::info('Cashfree payout initiated', [
                    'transfer_id' => $request->transactionId,
                    'reference_id' => $data['data']['referenceId'] ?? null,
                    'status' => $data['status'] ?? null,
                ]);

                return PayoutResponse::success(
                    status: PayoutResponse::STATUS_PROCESSING,
                    message: 'Payout initiated successfully',
                    transactionId: $request->transactionId,
                    providerPayoutId: $data['data']['referenceId'] ?? null,
                    metadata: $data
                );
            }

            $errorMessage = $data['message'] ?? 'Transfer request failed';
            Log::error('Cashfree payout failed', [
                'response' => $data,
                'transfer_id' => $request->transactionId,
            ]);

            return PayoutResponse::failed($errorMessage, $request->transactionId);
        } catch (\Exception $e) {
            Log::error('Cashfree payout exception', [
                'error' => $e->getMessage(),
                'transfer_id' => $request->transactionId,
            ]);

            return PayoutResponse::failed('Payout error: '.$e->getMessage());
        }
    }

    /**
     * Check payout status
     */
    public function checkStatus(string $payoutId): PayoutResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PayoutResponse::failed('Cashfree Payouts not configured');
        }

        try {
            $response = Http::withHeaders($this->getAuthHeaders($integration))
                ->timeout(30)
                ->get($this->getBaseUrl($integration).'/getTransferStatus', [
                    'transferId' => $payoutId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $transfer = $data['data']['transfer'] ?? [];

                $providerStatus = $transfer['status'] ?? 'UNKNOWN';
                $status = $this->mapStatus($providerStatus);

                Log::info('Cashfree payout status', [
                    'transfer_id' => $payoutId,
                    'provider_status' => $providerStatus,
                    'mapped_status' => $status,
                ]);

                return PayoutResponse::success(
                    status: $status,
                    message: 'Status retrieved',
                    transactionId: $payoutId,
                    providerPayoutId: $transfer['referenceId'] ?? null,
                    utrNumber: $transfer['utr'] ?? null,
                    metadata: $transfer
                );
            }

            return PayoutResponse::failed('Status check failed', $payoutId);
        } catch (\Exception $e) {
            Log::error('Cashfree status check exception', [
                'error' => $e->getMessage(),
                'transfer_id' => $payoutId,
            ]);

            return PayoutResponse::failed('Status check error: '.$e->getMessage());
        }
    }

    /**
     * Get account balance
     */
    public function getBalance(): ?array
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->getAuthHeaders($integration))
                ->timeout(30)
                ->get($this->getBaseUrl($integration).'/getBalance');

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Cashfree balance check exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Add a beneficiary to Cashfree
     */
    public function addBeneficiary(BeneficiaryAccount $beneficiary, ?Integration $integration = null): array
    {
        $integration = $integration ?? $this->getIntegration();
        if (! $integration) {
            return ['success' => false, 'message' => 'Cashfree Payouts not configured'];
        }

        try {
            $beneId = 'BENE-'.$beneficiary->id.'-'.substr(md5((string) $beneficiary->id), 0, 6);

            $payload = [
                'beneId' => $beneId,
                'name' => $beneficiary->holder_name,
                'email' => $beneficiary->accountable?->email ?? 'beneficiary@mintreu.com',
                'phone' => $this->formatPhone($beneficiary->accountable?->mobile),
                'address1' => 'India',
            ];

            if ($beneficiary->isBank()) {
                $payload['bankAccount'] = $beneficiary->account_number;
                $payload['ifsc'] = $beneficiary->ifsc_code;
            } else {
                $payload['vpa'] = $beneficiary->upi_id;
            }

            $response = Http::withHeaders($this->getAuthHeaders($integration))
                ->timeout(30)
                ->post($this->getBaseUrl($integration).'/addBeneficiary', $payload);

            $data = $response->json();

            // 409 means beneficiary already exists - that's fine
            if ($response->successful() || ($data['subCode'] ?? null) === '409') {
                Log::info('Cashfree beneficiary added/exists', [
                    'bene_id' => $beneId,
                    'beneficiary_account_id' => $beneficiary->id,
                ]);

                return [
                    'success' => true,
                    'bene_id' => $beneId,
                ];
            }

            Log::error('Cashfree add beneficiary failed', [
                'response' => $data,
                'beneficiary_account_id' => $beneficiary->id,
            ]);

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to add beneficiary',
            ];
        } catch (\Exception $e) {
            Log::error('Cashfree add beneficiary exception', [
                'error' => $e->getMessage(),
                'beneficiary_account_id' => $beneficiary->id,
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Remove a beneficiary from Cashfree
     */
    public function removeBeneficiary(string $beneId): bool
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return false;
        }

        try {
            $response = Http::withHeaders($this->getAuthHeaders($integration))
                ->timeout(30)
                ->post($this->getBaseUrl($integration).'/removeBeneficiary', [
                    'beneId' => $beneId,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Cashfree remove beneficiary exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get auth headers with bearer token
     */
    private function getAuthHeaders(Integration $integration): array
    {
        $token = $this->getBearerToken($integration);

        return [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get bearer token (cached)
     */
    private function getBearerToken(Integration $integration): ?string
    {
        $cacheKey = self::TOKEN_CACHE_KEY.'_'.$integration->id;

        return Cache::remember($cacheKey, self::TOKEN_CACHE_TTL, function () use ($integration) {
            return $this->authenticate($integration);
        });
    }

    /**
     * Authenticate with Cashfree to get bearer token
     */
    private function authenticate(Integration $integration): ?string
    {
        try {
            $response = Http::timeout(30)->post($this->getBaseUrl($integration).'/authorize', [
                'clientId' => $integration->getCredential('app_id'),
                'clientSecret' => $integration->getCredential('secret_key'),
            ]);

            if ($response->successful()) {
                $token = $response->json()['data']['token'] ?? null;

                if ($token) {
                    Log::info('Cashfree payout auth successful', [
                        'integration_id' => $integration->id,
                    ]);

                    return $token;
                }
            }

            Log::error('Cashfree payout auth failed', [
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Cashfree payout auth exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Map Cashfree status to our standard status
     */
    private function mapStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'SUCCESS' => PayoutResponse::STATUS_COMPLETED,
            'PENDING', 'RECEIVED' => PayoutResponse::STATUS_PROCESSING,
            'FAILED' => PayoutResponse::STATUS_FAILED,
            'REVERSED' => PayoutResponse::STATUS_REVERSED,
            'CANCELLED' => PayoutResponse::STATUS_CANCELLED,
            default => PayoutResponse::STATUS_PENDING,
        };
    }

    /**
     * Get the integration configuration
     */
    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::query()
                ->bySlug('cashfree')
                ->ofType(Integration::TYPE_PAYOUT)
                ->active()
                ->first();
        }

        return $this->integration;
    }

    /**
     * Get base URL based on environment
     */
    private function getBaseUrl(Integration $integration): string
    {
        return $integration->is_sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    /**
     * Format phone number (10 digits)
     */
    private function formatPhone(?string $phone): string
    {
        if (! $phone) {
            return '9999999999';
        }

        $phone = preg_replace('/^\+?91/', '', $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) >= 10) {
            return substr($phone, -10);
        }

        return '9999999999';
    }

    /**
     * Clear cached data (useful for testing)
     */
    public function clearCache(): void
    {
        $this->integration = null;
        if ($this->integration) {
            Cache::forget(self::TOKEN_CACHE_KEY.'_'.$this->integration->id);
        }
    }
}

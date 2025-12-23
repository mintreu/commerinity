<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Models\BeneficiaryAccount;
use App\Models\Integration;
use App\Services\Payment\Contracts\PayoutProviderInterface;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RazorpayPayoutProvider - RazorpayX Payout Integration
 *
 * Backup payout provider for India.
 * Supports: Bank Transfer (IMPS/NEFT/RTGS), UPI
 *
 * RazorpayX requires:
 * 1. Contact - Customer record
 * 2. Fund Account - Bank/UPI account linked to contact
 * 3. Payout - Transfer to fund account
 *
 * @see https://razorpay.com/docs/api/x/
 */
final class RazorpayPayoutProvider implements PayoutProviderInterface
{
    private const API_URL = 'https://api.razorpay.com/v1';

    private ?Integration $integration = null;

    public function getSlug(): string
    {
        return 'razorpay';
    }

    public function getName(): string
    {
        return 'RazorpayX Payouts';
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
            return PayoutResponse::failed('RazorpayX Payouts not configured');
        }

        // Step 1: Load beneficiary account
        $beneficiary = BeneficiaryAccount::find($request->beneficiaryAccountId);
        if (! $beneficiary) {
            return PayoutResponse::failed('Beneficiary account not found');
        }

        if (! $beneficiary->canReceivePayout()) {
            return PayoutResponse::failed('Beneficiary account is not verified for payouts');
        }

        // Step 2: Ensure fund account exists in Razorpay
        $fundAccountId = $beneficiary->metadata['razorpay_fund_account_id'] ?? null;

        if (! $fundAccountId) {
            $setupResult = $this->setupBeneficiary($beneficiary, $integration);
            if (! $setupResult['success']) {
                return PayoutResponse::failed($setupResult['message']);
            }
            $fundAccountId = $setupResult['fund_account_id'];

            // Store for future use
            $beneficiary->update([
                'metadata' => array_merge($beneficiary->metadata ?? [], [
                    'razorpay_contact_id' => $setupResult['contact_id'],
                    'razorpay_fund_account_id' => $fundAccountId,
                ]),
            ]);
        }

        // Step 3: Create payout
        try {
            $accountNumber = $integration->getCredential('account_number');
            if (! $accountNumber) {
                return PayoutResponse::failed('RazorpayX account number not configured');
            }

            $transferMode = $this->getTransferMode($beneficiary, $request->amountInPaisa);

            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/payouts', [
                    'account_number' => $accountNumber,
                    'fund_account_id' => $fundAccountId,
                    'amount' => $request->amountInPaisa, // Razorpay expects paisa
                    'currency' => $request->currency,
                    'mode' => $transferMode,
                    'purpose' => 'payout',
                    'queue_if_low_balance' => true,
                    'reference_id' => $request->transactionId,
                    'narration' => substr($request->description ?? $request->purpose ?? 'Payout', 0, 30),
                    'notes' => [
                        'user_id' => (string) $request->userId,
                        'transaction_id' => $request->transactionId,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('RazorpayX payout initiated', [
                    'payout_id' => $data['id'] ?? null,
                    'reference_id' => $request->transactionId,
                    'status' => $data['status'] ?? null,
                ]);

                $status = $this->mapStatus($data['status'] ?? 'processing');

                return PayoutResponse::success(
                    status: $status,
                    message: 'Payout initiated successfully',
                    transactionId: $request->transactionId,
                    providerPayoutId: $data['id'] ?? null,
                    utrNumber: $data['utr'] ?? null,
                    metadata: $data
                );
            }

            $errorMessage = $response->json()['error']['description'] ?? 'Payout request failed';
            Log::error('RazorpayX payout failed', [
                'response' => $response->json(),
                'reference_id' => $request->transactionId,
            ]);

            return PayoutResponse::failed($errorMessage, $request->transactionId);
        } catch (\Exception $e) {
            Log::error('RazorpayX payout exception', [
                'error' => $e->getMessage(),
                'reference_id' => $request->transactionId,
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
            return PayoutResponse::failed('RazorpayX Payouts not configured');
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->get(self::API_URL.'/payouts/'.$payoutId);

            if ($response->successful()) {
                $data = $response->json();

                $providerStatus = $data['status'] ?? 'unknown';
                $status = $this->mapStatus($providerStatus);

                Log::info('RazorpayX payout status', [
                    'payout_id' => $payoutId,
                    'provider_status' => $providerStatus,
                    'mapped_status' => $status,
                ]);

                return PayoutResponse::success(
                    status: $status,
                    message: 'Status retrieved',
                    transactionId: $data['reference_id'] ?? $payoutId,
                    providerPayoutId: $payoutId,
                    utrNumber: $data['utr'] ?? null,
                    metadata: $data
                );
            }

            return PayoutResponse::failed('Status check failed', $payoutId);
        } catch (\Exception $e) {
            Log::error('RazorpayX status check exception', [
                'error' => $e->getMessage(),
                'payout_id' => $payoutId,
            ]);

            return PayoutResponse::failed('Status check error: '.$e->getMessage());
        }
    }

    /**
     * Setup beneficiary in Razorpay (Contact + Fund Account)
     */
    public function setupBeneficiary(BeneficiaryAccount $beneficiary, ?Integration $integration = null): array
    {
        $integration = $integration ?? $this->getIntegration();
        if (! $integration) {
            return ['success' => false, 'message' => 'RazorpayX not configured'];
        }

        try {
            // Step 1: Create Contact
            $contactResponse = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/contacts', [
                    'name' => $beneficiary->holder_name,
                    'email' => $beneficiary->accountable?->email ?? 'beneficiary@mintreu.com',
                    'contact' => $this->formatPhone($beneficiary->accountable?->mobile),
                    'type' => 'customer',
                    'reference_id' => 'BENE-'.$beneficiary->id,
                    'notes' => [
                        'beneficiary_id' => (string) $beneficiary->id,
                    ],
                ]);

            if (! $contactResponse->successful()) {
                $error = $contactResponse->json()['error']['description'] ?? 'Contact creation failed';
                Log::error('RazorpayX contact creation failed', [
                    'response' => $contactResponse->json(),
                ]);

                return ['success' => false, 'message' => $error];
            }

            $contactId = $contactResponse->json()['id'];

            // Step 2: Create Fund Account
            $fundAccountPayload = [
                'contact_id' => $contactId,
                'account_type' => $beneficiary->isUpi() ? 'vpa' : 'bank_account',
            ];

            if ($beneficiary->isUpi()) {
                $fundAccountPayload['vpa'] = [
                    'address' => $beneficiary->upi_id,
                ];
            } else {
                $fundAccountPayload['bank_account'] = [
                    'name' => $beneficiary->holder_name,
                    'ifsc' => $beneficiary->ifsc_code,
                    'account_number' => $beneficiary->account_number,
                ];
            }

            $fundAccountResponse = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/fund_accounts', $fundAccountPayload);

            if (! $fundAccountResponse->successful()) {
                $error = $fundAccountResponse->json()['error']['description'] ?? 'Fund account creation failed';
                Log::error('RazorpayX fund account creation failed', [
                    'response' => $fundAccountResponse->json(),
                ]);

                return ['success' => false, 'message' => $error];
            }

            $fundAccountId = $fundAccountResponse->json()['id'];

            Log::info('RazorpayX beneficiary setup complete', [
                'beneficiary_id' => $beneficiary->id,
                'contact_id' => $contactId,
                'fund_account_id' => $fundAccountId,
            ]);

            return [
                'success' => true,
                'contact_id' => $contactId,
                'fund_account_id' => $fundAccountId,
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayX beneficiary setup exception', [
                'error' => $e->getMessage(),
                'beneficiary_id' => $beneficiary->id,
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
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

        $accountNumber = $integration->getCredential('account_number');
        if (! $accountNumber) {
            return null;
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->get(self::API_URL.'/balance/'.$accountNumber);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('RazorpayX balance check exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Cancel a pending payout
     */
    public function cancel(string $payoutId): PayoutResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PayoutResponse::failed('RazorpayX not configured');
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/payouts/'.$payoutId.'/cancel');

            if ($response->successful()) {
                return PayoutResponse::success(
                    status: PayoutResponse::STATUS_CANCELLED,
                    message: 'Payout cancelled',
                    providerPayoutId: $payoutId
                );
            }

            return PayoutResponse::failed('Cancel failed');
        } catch (\Exception $e) {
            return PayoutResponse::failed('Cancel error: '.$e->getMessage());
        }
    }

    /**
     * Map Razorpay status to our standard status
     */
    private function mapStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'processed' => PayoutResponse::STATUS_COMPLETED,
            'processing', 'queued', 'pending' => PayoutResponse::STATUS_PROCESSING,
            'failed' => PayoutResponse::STATUS_FAILED,
            'reversed' => PayoutResponse::STATUS_REVERSED,
            'cancelled' => PayoutResponse::STATUS_CANCELLED,
            default => PayoutResponse::STATUS_PENDING,
        };
    }

    /**
     * Get transfer mode based on account type and amount
     */
    private function getTransferMode(BeneficiaryAccount $beneficiary, int $amountInPaisa): string
    {
        if ($beneficiary->isUpi()) {
            return 'UPI';
        }

        // For bank transfers, choose based on amount
        $amountInRupees = $amountInPaisa / 100;

        // IMPS: Up to 5 lakh, instant
        // NEFT: Any amount, batch processing
        // RTGS: Above 2 lakh, real-time (only during banking hours)
        if ($amountInRupees <= 200000) {
            return 'IMPS';
        }

        return 'NEFT';
    }

    /**
     * Get the integration configuration
     */
    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::query()
                ->bySlug('razorpay')
                ->ofType(Integration::TYPE_PAYOUT)
                ->active()
                ->first();
        }

        return $this->integration;
    }

    /**
     * Format phone number
     */
    private function formatPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Remove +91 prefix if present
        $phone = preg_replace('/^\+?91/', '', $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return strlen($phone) === 10 ? $phone : null;
    }

    /**
     * Clear cached integration (useful for testing)
     */
    public function clearCache(): void
    {
        $this->integration = null;
    }
}

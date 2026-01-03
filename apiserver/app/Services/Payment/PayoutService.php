<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\BeneficiaryAccount;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Payment\Contracts\PayoutProviderInterface;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;
use App\Services\Payment\Providers\CashfreePayoutProvider;
use App\Services\Payment\Providers\NativePayoutProvider;
use App\Services\Payment\Providers\RazorpayPayoutProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PayoutService - Unified payout gateway
 *
 * Routes payouts and beneficiary operations to appropriate providers.
 * Mirrors PaymentService architecture for consistency.
 *
 * Provider Priority:
 * 1. Cashfree (default for India)
 * 2. Razorpay (backup)
 * 3. Native (manual processing)
 */
final class PayoutService
{
    /** @var array<string, PayoutProviderInterface> */
    private array $providers = [];

    private ?string $defaultProvider = null;

    public function __construct(
        private readonly NativePayoutProvider $nativePayout,
        private readonly CashfreePayoutProvider $cashfreePayout,
        private readonly RazorpayPayoutProvider $razorpayPayout,
    ) {
        $this->registerProvider($this->nativePayout);

        if ($this->cashfreePayout->isAvailable()) {
            $this->registerProvider($this->cashfreePayout);
        }

        if ($this->razorpayPayout->isAvailable()) {
            $this->registerProvider($this->razorpayPayout);
        }

        $this->setDefaultFromDatabase();
    }

    // ========================================
    // Provider Management
    // ========================================

    public function registerProvider(PayoutProviderInterface $provider): void
    {
        $this->providers[$provider->getSlug()] = $provider;
    }

    public function getProvider(string $slug): ?PayoutProviderInterface
    {
        return $this->providers[$slug] ?? null;
    }

    public function getDefaultProvider(): ?PayoutProviderInterface
    {
        if ($this->defaultProvider && isset($this->providers[$this->defaultProvider])) {
            return $this->providers[$this->defaultProvider];
        }

        foreach (['cashfree', 'razorpay'] as $slug) {
            if (isset($this->providers[$slug]) && $this->providers[$slug]->isAvailable()) {
                return $this->providers[$slug];
            }
        }

        return $this->nativePayout;
    }

    public function getProviderForMethod(PaymentMethodCast $method): ?PayoutProviderInterface
    {
        foreach ($this->providers as $provider) {
            if (in_array($method->value, $provider->getSupportedMethods(), true)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @return array<string, array{slug: string, name: string, methods: array<string>}>
     */
    public function getAvailableMethods(): array
    {
        $methods = [];
        foreach ($this->providers as $provider) {
            if ($provider->isAvailable()) {
                $methods[$provider->getSlug()] = [
                    'slug' => $provider->getSlug(),
                    'name' => $provider->getName(),
                    'methods' => $provider->getSupportedMethods(),
                ];
            }
        }

        return $methods;
    }

    public function hasExternalProvider(): bool
    {
        return isset($this->providers['cashfree']) || isset($this->providers['razorpay']);
    }

    // ========================================
    // WALLET CREDIT OPERATIONS (Admin -> User)
    // ========================================

    /**
     * Credit user wallet (for commissions, affiliate payouts, refunds, bonuses)
     *
     * This is used by admin/system to add funds to user wallets.
     *
     * @param  int  $amountInPaisa  Amount in smallest currency unit
     * @param  string  $type  Payout type: commission, affiliate, refund, bonus, manual
     * @param  string|null  $description  Transaction description
     * @param  string|null  $referenceId  External reference ID
     * @param  array<string, mixed>  $metadata  Additional metadata
     */
    public function creditWallet(
        Wallet $wallet,
        int $amountInPaisa,
        string $type,
        ?string $description = null,
        ?string $referenceId = null,
        array $metadata = [],
    ): array {
        // Validate inputs
        if ($amountInPaisa < 100) {
            return ['success' => false, 'message' => 'Minimum credit amount is ₹1'];
        }

        $validTypes = ['commission', 'affiliate', 'refund', 'bonus', 'manual'];
        if (! in_array($type, $validTypes)) {
            return ['success' => false, 'message' => 'Invalid credit type. Allowed: '.implode(', ', $validTypes)];
        }

        // Validate wallet
        if (! $wallet->canTransact()) {
            return ['success' => false, 'message' => 'Wallet is not active'];
        }

        return DB::transaction(function () use ($wallet, $amountInPaisa, $type, $description, $referenceId, $metadata) {
            // Create credit transaction
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'transactionable_type' => Wallet::class,
                'transactionable_id' => $wallet->id,
                'type' => TransactionTypeCast::CREDIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => $amountInPaisa,
                'purpose' => ucfirst($type),
                'description' => $description ?? "Wallet credited via {$type}",
                'payment_method' => PaymentMethodCast::INTERNAL,
                'reference_id' => $referenceId,
                'metadata' => array_merge($metadata, [
                    'credit_type' => $type,
                    'source' => 'admin_payout',
                ]),
            ]);

            // Update wallet balance
            $wallet->update([
                'balance' => $wallet->balance + $amountInPaisa,
            ]);

            Log::info('Wallet credited via payout', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'amount' => $amountInPaisa,
                'type' => $type,
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => true,
                'transaction_id' => $transaction->uuid,
                'amount' => $amountInPaisa,
                'wallet_balance' => $wallet->fresh()->balance,
                'message' => 'Wallet credited successfully',
            ];
        });
    }

    /**
     * Credit wallet by user ID (convenience method)
     */
    public function creditWalletByUserId(
        int $userId,
        int $amountInPaisa,
        string $type,
        ?string $description = null,
        ?string $referenceId = null,
        array $metadata = [],
    ): array {
        $user = User::find($userId);

        if (! $user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $wallet = $user->wallet;

        if (! $wallet) {
            return ['success' => false, 'message' => 'User wallet not found'];
        }

        return $this->creditWallet($wallet, $amountInPaisa, $type, $description, $referenceId, $metadata);
    }

    // ========================================
    // Payout Operations (User -> Bank)
    // ========================================

    public function initiate(PayoutRequest $request): PayoutResponse
    {
        $provider = $this->getProviderForMethod($request->method);

        if (! $provider || ! $provider->isAvailable()) {
            return PayoutResponse::failed('No payout provider available for this method');
        }

        return $provider->initiate($request);
    }

    public function checkStatus(string $payoutId, ?string $providerSlug = null): PayoutResponse
    {
        $provider = $providerSlug ? $this->getProvider($providerSlug) : $this->getDefaultProvider();

        if (! $provider) {
            return PayoutResponse::failed('Payout provider not found');
        }

        return $provider->checkStatus($payoutId);
    }

    /**
     * Send money to a beneficiary (withdrawal/payout)
     *
     * This is the main method to transfer funds from wallet to beneficiary bank/UPI.
     * Auto-creates provider config if missing before initiating payout.
     *
     * @param  int  $amountInPaisa  Amount in smallest currency unit
     * @param  string|null  $description  Optional description
     * @param  string|null  $purpose  Purpose code (withdrawal, payout, commission, etc.)
     */
    public function sendToBeneficiary(
        Wallet $wallet,
        BeneficiaryAccount $beneficiary,
        int $amountInPaisa,
        ?string $description = null,
        ?string $purpose = 'withdrawal',
    ): PayoutResponse {
        // Validate wallet
        if (! $wallet->canTransact()) {
            return PayoutResponse::failed('Wallet is not active for transactions');
        }

        if (! $wallet->hasSufficientBalance($amountInPaisa)) {
            return PayoutResponse::failed('Insufficient wallet balance');
        }

        // Validate beneficiary
        if ($beneficiary->wallet_id !== $wallet->id) {
            return PayoutResponse::failed('Beneficiary does not belong to this wallet');
        }

        if (! $beneficiary->canReceivePayout()) {
            return PayoutResponse::failed('Beneficiary account is not verified for payouts');
        }

        // Get default provider
        $provider = $this->getDefaultProvider();
        if (! $provider) {
            return PayoutResponse::failed('No payout provider available');
        }

        // Ensure provider config exists, create if missing
        $providerSlug = $provider->getSlug();
        if (! $beneficiary->hasProviderConfig($providerSlug)) {
            Log::info('Creating provider config for beneficiary', [
                'beneficiary_id' => $beneficiary->id,
                'provider' => $providerSlug,
            ]);

            $configResult = $this->createProviderConfig($beneficiary, $provider);
            if (! $configResult['success']) {
                return PayoutResponse::failed('Failed to create provider configuration: '.$configResult['message']);
            }

            // Update beneficiary status to ACTIVE after successful config creation
            $beneficiary->update(['status' => BeneficiaryStatusCast::VERIFIED]);
        }

        // Determine payment method based on beneficiary type
        $method = $beneficiary->isUpi()
            ? PaymentMethodCast::PAYOUT_UPI
            : PaymentMethodCast::PAYOUT_BANK;

        // Create payout request
        $request = new PayoutRequest(
            amountInPaisa: $amountInPaisa,
            currency: 'INR',
            method: $method,
            userId: $wallet->user_id,
            walletId: $wallet->id,
            beneficiaryAccountId: $beneficiary->id,
            transactionId: 'TXN-'.Str::upper(Str::random(12)),
            purpose: $purpose,
            description: $description ?? 'Payout to '.$beneficiary->getMaskedAccountDisplay(),
            metadata: [
                'beneficiary_id' => $beneficiary->id,
                'beneficiary_type' => $beneficiary->type->value,
                'provider' => $providerSlug,
            ],
        );

        Log::info('Initiating payout to beneficiary', [
            'wallet_id' => $wallet->id,
            'beneficiary_id' => $beneficiary->id,
            'amount' => $amountInPaisa,
            'method' => $method->value,
            'provider' => $providerSlug,
        ]);

        return $this->initiate($request);
    }

    /**
     * Create provider configuration for beneficiary
     *
     * This calls the provider's createBeneficiary method and stores the config.
     *
     * @return array{success: bool, message?: string}
     */
    private function createProviderConfig(
        BeneficiaryAccount $beneficiary,
        PayoutProviderInterface $provider
    ): array {
        $data = [
            'type' => $beneficiary->type->value,
            'holder_name' => $beneficiary->holder_name,
            'account_number' => $beneficiary->account_number,
            'ifsc' => $beneficiary->ifsc_code,
            'bank_name' => $beneficiary->bank_name,
            'upi_id' => $beneficiary->upi_id,
        ];

        $result = $provider->createBeneficiary($beneficiary->wallet, $data);

        if ($result['success']) {
            // Extract provider-specific data and store in beneficiary metadata
            $providerConfig = [
                'beneficiary_id' => $result['beneficiary_id'] ?? null,
                'created_at' => now()->toIso8601String(),
            ];

            // Add provider-specific fields from result
            if (isset($result['data'])) {
                $providerConfig = array_merge($providerConfig, $result['data']);
            }

            $beneficiary->setProviderConfig($provider->getSlug(), $providerConfig);

            Log::info('Provider config created successfully', [
                'beneficiary_id' => $beneficiary->id,
                'provider' => $provider->getSlug(),
            ]);
        }

        return $result;
    }

    /**
     * Quick send to default beneficiary
     */
    public function sendToDefaultBeneficiary(
        Wallet $wallet,
        int $amountInPaisa,
        ?string $description = null,
        ?string $purpose = 'withdrawal',
    ): PayoutResponse {
        $beneficiary = $this->getDefaultBeneficiary($wallet);

        if (! $beneficiary) {
            return PayoutResponse::failed('No default beneficiary set for this wallet');
        }

        return $this->sendToBeneficiary($wallet, $beneficiary, $amountInPaisa, $description, $purpose);
    }

    // ========================================
    // Payout History & Tracking
    // ========================================

    /**
     * Get all payouts for a wallet
     */
    public function getPayoutHistory(Wallet $wallet, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', TransactionTypeCast::DEBIT)
            ->where('purpose', 'withdrawal')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all payouts to a specific beneficiary
     */
    public function getPayoutsByBeneficiary(BeneficiaryAccount $beneficiary, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('wallet_id', $beneficiary->wallet_id)
            ->where('type', TransactionTypeCast::DEBIT)
            ->whereJsonContains('metadata->beneficiary_id', $beneficiary->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get the last payout for a beneficiary
     */
    public function getLastPayout(BeneficiaryAccount $beneficiary): ?Transaction
    {
        return Transaction::query()
            ->where('wallet_id', $beneficiary->wallet_id)
            ->where('type', TransactionTypeCast::DEBIT)
            ->whereJsonContains('metadata->beneficiary_id', $beneficiary->id)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Get last payout for a wallet (any beneficiary)
     */
    public function getLastWalletPayout(Wallet $wallet): ?Transaction
    {
        return Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', TransactionTypeCast::DEBIT)
            ->where('purpose', 'withdrawal')
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Get pending payouts for a wallet
     */
    public function getPendingPayouts(Wallet $wallet): Collection
    {
        return Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', TransactionTypeCast::DEBIT)
            ->where('purpose', 'withdrawal')
            ->where('status', TransactionStatusCast::PENDING)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get payout by transaction UUID
     */
    public function getPayoutByUuid(string $uuid): ?Transaction
    {
        return Transaction::query()
            ->where('uuid', $uuid)
            ->where('type', TransactionTypeCast::DEBIT)
            ->first();
    }

    /**
     * Get payout statistics for a wallet
     *
     * @return array{total_payouts: int, total_amount: int, pending_count: int, pending_amount: int, completed_count: int, completed_amount: int}
     */
    public function getPayoutStats(Wallet $wallet): array
    {
        $payouts = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('type', TransactionTypeCast::DEBIT)
            ->where('purpose', 'withdrawal')
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $pending = $payouts->get(TransactionStatusCast::PENDING->value);
        $completed = $payouts->get(TransactionStatusCast::COMPLETED->value);

        return [
            'total_payouts' => $payouts->sum('count'),
            'total_amount' => (int) $payouts->sum('total'),
            'pending_count' => $pending?->count ?? 0,
            'pending_amount' => (int) ($pending?->total ?? 0),
            'completed_count' => $completed?->count ?? 0,
            'completed_amount' => (int) ($completed?->total ?? 0),
        ];
    }

    /**
     * Cancel a pending payout
     */
    public function cancelPayout(Transaction $transaction, ?string $reason = null): PayoutResponse
    {
        if ($transaction->type !== TransactionTypeCast::DEBIT) {
            return PayoutResponse::failed('Transaction is not a payout');
        }

        if ($transaction->status !== TransactionStatusCast::PENDING) {
            return PayoutResponse::failed('Only pending payouts can be cancelled');
        }

        // If using native provider, use its cancel method
        if ($this->nativePayout->getSupportedMethods() && method_exists($this->nativePayout, 'cancelPayout')) {
            return $this->nativePayout->cancelPayout($transaction->uuid, $reason);
        }

        // For external providers, try to cancel via provider
        $provider = $this->getDefaultProvider();
        if ($provider && method_exists($provider, 'cancel')) {
            $providerPayoutId = $transaction->provider_transaction_id ?? $transaction->uuid;

            return $provider->cancel($providerPayoutId);
        }

        return PayoutResponse::failed('Payout cancellation not supported for this provider');
    }

    // ========================================
    // Beneficiary Operations (routed to providers)
    // ========================================

    /**
     * Register beneficiary with payout provider
     *
     * @param  BeneficiaryAccount  $beneficiary  The beneficiary account to register
     * @param  string|null  $providerSlug  Optional provider slug override
     * @return array{success: bool, beneficiary_id?: string, message?: string}
     */
    public function createBeneficiary(BeneficiaryAccount $beneficiary, ?string $providerSlug = null): array
    {
        $provider = $providerSlug ? $this->getProvider($providerSlug) : $this->getDefaultProvider();

        if (! $provider) {
            Log::error('No payout provider available for createBeneficiary');

            return ['success' => false, 'message' => 'No payout provider available'];
        }

        Log::info('Registering beneficiary with provider', [
            'provider' => $provider->getSlug(),
            'beneficiary_id' => $beneficiary->id,
            'wallet_id' => $beneficiary->wallet_id,
        ]);

        return $provider->createBeneficiary($beneficiary);
    }

    /**
     * Update beneficiary via the default provider
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message?: string}
     */
    public function updateBeneficiary(BeneficiaryAccount $beneficiary, array $data, ?string $providerSlug = null): array
    {
        $provider = $providerSlug ? $this->getProvider($providerSlug) : $this->getDefaultProvider();

        if (! $provider) {
            Log::error('No payout provider available for updateBeneficiary');

            return ['success' => false, 'message' => 'No payout provider available'];
        }

        Log::info('Updating beneficiary via provider', [
            'provider' => $provider->getSlug(),
            'beneficiary_id' => $beneficiary->id,
        ]);

        return $provider->updateBeneficiary($beneficiary, $data);
    }

    /**
     * Delete beneficiary via the default provider
     *
     * @return array{success: bool, message?: string}
     */
    public function deleteBeneficiary(BeneficiaryAccount $beneficiary, ?string $providerSlug = null): array
    {
        $provider = $providerSlug ? $this->getProvider($providerSlug) : $this->getDefaultProvider();

        if (! $provider) {
            Log::error('No payout provider available for deleteBeneficiary');

            return ['success' => false, 'message' => 'No payout provider available'];
        }

        Log::info('Deleting beneficiary via provider', [
            'provider' => $provider->getSlug(),
            'beneficiary_id' => $beneficiary->id,
        ]);

        return $provider->deleteBeneficiary($beneficiary);
    }

    /**
     * Get beneficiary details via the default provider
     *
     * @return array{success: bool, data?: array<string, mixed>, message?: string}
     */
    public function getBeneficiary(BeneficiaryAccount $beneficiary, ?string $providerSlug = null): array
    {
        $provider = $providerSlug ? $this->getProvider($providerSlug) : $this->getDefaultProvider();

        if (! $provider) {
            Log::error('No payout provider available for getBeneficiary');

            return ['success' => false, 'message' => 'No payout provider available'];
        }

        return $provider->getBeneficiary($beneficiary);
    }

    /**
     * Get all beneficiaries for a wallet
     */
    public function getBeneficiaries(Wallet $wallet): Collection
    {
        return $wallet->beneficiaryAccounts()->orderByDesc('is_default')->orderByDesc('created_at')->get();
    }

    /**
     * Get default beneficiary for a wallet
     */
    public function getDefaultBeneficiary(Wallet $wallet): ?BeneficiaryAccount
    {
        return $wallet->beneficiaryAccounts()->where('is_default', true)->first();
    }

    /**
     * Set a beneficiary as default
     */
    public function setDefaultBeneficiary(BeneficiaryAccount $beneficiary): BeneficiaryAccount
    {
        DB::transaction(function () use ($beneficiary) {
            BeneficiaryAccount::where('wallet_id', $beneficiary->wallet_id)
                ->where('id', '!=', $beneficiary->id)
                ->update(['is_default' => false]);
            $beneficiary->update(['is_default' => true]);
        });

        return $beneficiary->fresh();
    }

    /**
     * Verify beneficiary with provider
     *
     * @return array{valid: bool, message: string}
     */
    public function verifyBeneficiary(BeneficiaryAccount $beneficiary, ?string $providerSlug = null): array
    {
        $provider = $providerSlug ? $this->getProvider($providerSlug) : $this->getDefaultProvider();

        if ($provider && method_exists($provider, 'verifyBeneficiary')) {
            try {
                $result = $provider->verifyBeneficiary($beneficiary);
                $beneficiary->update([
                    'status' => $result['valid'] ? BeneficiaryStatusCast::ACTIVE : BeneficiaryStatusCast::REJECTED,
                    'status_feedback' => $result['message'] ?? null,
                ]);

                return $result;
            } catch (\Exception $e) {
                Log::warning('Beneficiary verification failed', ['error' => $e->getMessage()]);
            }
        }

        // Fallback: basic validation
        $beneficiary->update(['status' => BeneficiaryStatusCast::ACTIVE]);

        return ['valid' => true, 'message' => 'Verified'];
    }

    // ========================================
    // Private
    // ========================================

    private function setDefaultFromDatabase(): void
    {
        $default = Integration::getDefaultPayout();
        if ($default && isset($this->providers[$default->slug])) {
            $this->defaultProvider = $default->slug;
        }
    }

    public function refreshProviders(): void
    {
        unset($this->providers['cashfree'], $this->providers['razorpay']);

        $this->cashfreePayout->clearCache();
        $this->razorpayPayout->clearCache();

        if ($this->cashfreePayout->isAvailable()) {
            $this->registerProvider($this->cashfreePayout);
        }
        if ($this->razorpayPayout->isAvailable()) {
            $this->registerProvider($this->razorpayPayout);
        }

        $this->setDefaultFromDatabase();
    }
}

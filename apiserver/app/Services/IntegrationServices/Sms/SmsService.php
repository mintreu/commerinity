<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms;

use App\Models\Sms\SmsLog;
use App\Models\Sms\SmsProvider;
use App\Services\IntegrationServices\Sms\Contracts\SmsProviderInterface;
use App\Services\IntegrationServices\Sms\DTOs\BalanceInfo;
use App\Services\IntegrationServices\Sms\DTOs\DeliveryReport;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service Manager - Orchestrates SMS sending with auto-failover.
 *
 * Features:
 * - Database-backed provider configuration
 * - Auto-failover to next available provider
 * - Balance checking before bulk sends
 * - Request logging and analytics
 * - Circuit breaker pattern
 */
final class SmsService
{
    private ?SmsProviderInterface $currentProvider = null;

    /**
     * @var Collection<int, SmsProvider>|null
     */
    private ?Collection $providers = null;

    /**
     * Send SMS with auto-failover.
     */
    public function send(SmsRequest $request): SmsResponse
    {
        $providers = $this->getServiceableProviders($request->getRecipientCount());

        if ($providers->isEmpty()) {
            Log::error('SmsService: No serviceable providers available');

            return SmsResponse::providerUnavailable('none', 'No SMS providers available');
        }

        // Try each provider in order until one succeeds
        foreach ($providers as $providerModel) {
            $driver = $providerModel->createDriver();

            // Create log entry
            $log = $this->createLog($request, $providerModel);

            try {
                $response = $driver->send($request);

                // Update log with response
                $this->updateLog($log, $response, $providerModel);

                if ($response->success) {
                    return $response;
                }

                // If insufficient balance, try next provider
                if ($response->errorCode === 'INSUFFICIENT_BALANCE') {
                    Log::warning("SmsService: Provider {$providerModel->slug} has insufficient balance, trying next");

                    continue;
                }

            } catch (\Throwable $e) {
                Log::error("SmsService: Provider {$providerModel->slug} failed", [
                    'error' => $e->getMessage(),
                ]);

                $log->markAsFailed($e->getMessage(), 'EXCEPTION');
                $providerModel->recordFailure($e->getMessage());

                continue;
            }
        }

        return SmsResponse::failure('All SMS providers failed');
    }

    /**
     * Send OTP SMS.
     */
    public function sendOtp(string $phone, string $otp, ?int $userId = null): SmsResponse
    {
        $request = SmsRequest::otp($phone, $otp);

        if ($userId) {
            $request = new SmsRequest(
                recipients: [$phone],
                message: $otp,
                type: 'otp',
                templateSlug: 'otp-verification',
                variables: ['otp' => $otp],
                user: \App\Models\User::find($userId),
            );
        }

        return $this->send($request);
    }

    /**
     * Send welcome SMS.
     */
    public function sendWelcome(string $phone, string $name): SmsResponse
    {
        $appName = config('app.name');
        $message = "Welcome to {$appName}, {$name}! Your account has been created successfully. Start exploring and enjoy exclusive benefits!";

        return $this->sendSingle($phone, $message, 'transactional');
    }

    /**
     * Send single SMS (convenience method).
     */
    public function sendSingle(
        string $phone,
        string $message,
        string $type = 'transactional',
        ?int $userId = null,
    ): SmsResponse {
        $request = SmsRequest::single(
            recipient: $phone,
            message: $message,
            type: $type,
            user: $userId ? \App\Models\User::find($userId) : null,
        );

        return $this->send($request);
    }

    /**
     * Send bulk SMS with balance optimization.
     *
     * @param  array<string>  $phones
     */
    public function sendBulk(
        array $phones,
        string $message,
        string $type = 'promotional',
        ?string $templateSlug = null,
        ?array $variables = null,
    ): SmsResponse {
        $count = count($phones);

        // Check balance across all providers
        $provider = $this->getBestProviderForBulk($count);

        if (! $provider) {
            return SmsResponse::insufficientBalance(0, $count * 0.25);
        }

        // For large bulk sends, chunk to avoid timeout
        if ($count > 1000) {
            return $this->sendBulkChunked($phones, $message, $type, $templateSlug, $variables);
        }

        $request = SmsRequest::bulk(
            recipients: $phones,
            message: $message,
            type: $type,
            templateSlug: $templateSlug,
            variables: $variables,
        );

        return $this->send($request);
    }

    /**
     * Get balance from default provider.
     */
    public function getBalance(): BalanceInfo
    {
        $provider = $this->getDefaultProvider();

        if (! $provider) {
            return BalanceInfo::error('No default provider configured');
        }

        return $provider->createDriver()->getBalance();
    }

    /**
     * Get balance from all active providers.
     *
     * @return array<string, BalanceInfo>
     */
    public function getAllBalances(): array
    {
        $balances = [];

        foreach ($this->getActiveProviders() as $provider) {
            $balances[$provider->slug] = $provider->createDriver()->getBalance();
        }

        return $balances;
    }

    /**
     * Check if can send specific count of SMS.
     */
    public function canSend(int $count = 1): bool
    {
        return $this->getServiceableProviders($count)->isNotEmpty();
    }

    /**
     * Get delivery report.
     */
    public function getDeliveryReport(string $requestId): DeliveryReport
    {
        // Find the log entry to determine which provider was used
        $log = SmsLog::where('request_id', $requestId)->first();

        if (! $log || ! $log->sms_provider_id) {
            return DeliveryReport::error('Log not found', $requestId);
        }

        $provider = SmsProvider::find($log->sms_provider_id);

        if (! $provider) {
            return DeliveryReport::error('Provider not found', $requestId);
        }

        $report = $provider->createDriver()->getDeliveryReport($requestId);

        // Update log with delivery status
        if ($report->success) {
            if ($report->isDelivered()) {
                $log->markAsDelivered($report->status);
            } elseif ($report->isFailed()) {
                $log->markAsFailed($report->errorMessage ?? 'Delivery failed', $report->errorCode);
            }
        }

        return $report;
    }

    /**
     * Get SMS analytics.
     *
     * @return array<string, mixed>
     */
    public function getAnalytics(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $from ??= now()->startOfMonth();
        $to ??= now();

        return SmsLog::getAnalytics($from, $to);
    }

    /**
     * Get monthly expense projection for default provider.
     *
     * @return array<string, mixed>
     */
    public function getMonthlyProjection(): array
    {
        $provider = $this->getDefaultProvider();

        if (! $provider) {
            return ['error' => 'No default provider configured'];
        }

        return $provider->getMonthlyExpenseProjection();
    }

    /**
     * Get all expense projections across providers.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllExpenseProjections(): array
    {
        $projections = [];

        foreach ($this->getActiveProviders() as $provider) {
            $projections[$provider->slug] = $provider->getMonthlyExpenseProjection();
        }

        return $projections;
    }

    /**
     * Sync balance from provider API.
     */
    public function syncBalance(?string $providerSlug = null): BalanceInfo
    {
        $provider = $providerSlug
            ? SmsProvider::where('slug', $providerSlug)->first()
            : $this->getDefaultProvider();

        if (! $provider) {
            return BalanceInfo::error('Provider not found');
        }

        $driver = $provider->createDriver();
        $balance = $driver->getBalance();

        if ($balance->success) {
            $provider->update([
                'balance' => $balance->balance,
                'balance_checked_at' => now(),
            ]);
        }

        return $balance;
    }

    /**
     * Get current active provider slug.
     */
    public function getActiveProviderSlug(): string
    {
        return $this->getDefaultProvider()?->slug ?? 'log';
    }

    // =========================================================================
    // PROVIDER MANAGEMENT
    // =========================================================================

    /**
     * Get default provider.
     */
    private function getDefaultProvider(): ?SmsProvider
    {
        // Check for database provider first
        $dbProvider = SmsProvider::query()
            ->default()
            ->active()
            ->first();

        if ($dbProvider) {
            return $dbProvider;
        }

        // Fall back to config-based provider
        return $this->getConfigBasedProvider();
    }

    /**
     * Get active providers ordered by priority.
     *
     * @return Collection<int, SmsProvider>
     */
    private function getActiveProviders(): Collection
    {
        if ($this->providers !== null) {
            return $this->providers;
        }

        $this->providers = SmsProvider::active()->get();

        // If no database providers, create from config
        if ($this->providers->isEmpty()) {
            $configProvider = $this->getConfigBasedProvider();
            if ($configProvider) {
                $this->providers = collect([$configProvider]);
            }
        }

        return $this->providers;
    }

    /**
     * Get serviceable providers (active, healthy, has balance for count).
     *
     * @return Collection<int, SmsProvider>
     */
    private function getServiceableProviders(int $requiredCount = 1): Collection
    {
        $providers = $this->getActiveProviders();

        return $providers->filter(function (SmsProvider $provider) use ($requiredCount) {
            // Check circuit breaker
            if ($provider->consecutive_failures >= 5) {
                return false;
            }

            // For log provider in testing, always allow
            if ($provider->driver === 'log') {
                return true;
            }

            // Check balance
            return $provider->canSend($requiredCount);
        });
    }

    /**
     * Get best provider for bulk sends.
     */
    private function getBestProviderForBulk(int $count): ?SmsProvider
    {
        $providers = $this->getActiveProviders();

        // Find provider with enough balance and best success rate
        return $providers
            ->filter(fn (SmsProvider $p) => $p->canSend($count))
            ->sortByDesc('success_rate')
            ->first();
    }

    /**
     * Get provider from config (fallback).
     */
    private function getConfigBasedProvider(): ?SmsProvider
    {
        $providerName = config('services.sms.provider', 'log');

        // In testing, always use log
        if (app()->environment('testing')) {
            $providerName = 'log';
        }

        // Create a virtual provider model from config
        $provider = new SmsProvider([
            'name' => match ($providerName) {
                'fast2sms' => 'Fast2SMS',
                'log' => 'Log Provider',
                default => 'Unknown Provider',
            },
            'slug' => $providerName,
            'driver' => $providerName,
            'api_key' => config('services.sms.fast2sms.api_key'),
            'sender_id' => config('services.sms.fast2sms.sender_id'),
            'entity_id' => config('services.sms.fast2sms.entity_id'),
            'per_sms_cost' => (float) config('services.sms.fast2sms.per_sms_cost', 0.25),
            'min_balance_threshold' => (float) config('services.sms.fast2sms.min_balance_threshold', 10.0),
            'balance' => 999999.99, // Assume sufficient for config-based
            'is_active' => true,
            'is_default' => true,
            'priority' => 1,
        ]);

        // Don't persist
        $provider->exists = false;

        return $provider;
    }

    // =========================================================================
    // LOGGING
    // =========================================================================

    /**
     * Create log entry for SMS request.
     */
    private function createLog(SmsRequest $request, SmsProvider $provider): SmsLog
    {
        return SmsLog::create([
            'sms_provider_id' => $provider->id,
            'provider_slug' => $provider->slug,
            'recipient' => $request->getRecipient(),
            'message' => $request->message,
            'message_type' => $request->type,
            'template_code' => $request->templateSlug,
            'variables' => $request->variables,
            'user_id' => $request->user?->id,
            'loggable_type' => $request->loggable ? get_class($request->loggable) : null,
            'loggable_id' => $request->loggable?->getKey(),
            'status' => SmsLog::STATUS_PENDING,
            'source' => $request->source,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $request->metadata,
        ]);
    }

    /**
     * Update log entry with response.
     */
    private function updateLog(SmsLog $log, SmsResponse $response, SmsProvider $provider): void
    {
        if ($response->success) {
            $log->markAsSent($response->requestId, $response->messageId);
            $log->update(['cost' => $response->cost]);
            $provider->recordSuccess(1, $response->cost);
        } else {
            $log->markAsFailed($response->errorMessage ?? $response->message, $response->errorCode);
            $provider->recordFailure($response->message);
        }
    }

    /**
     * Send bulk SMS in chunks.
     */
    private function sendBulkChunked(
        array $phones,
        string $message,
        string $type,
        ?string $templateSlug,
        ?array $variables,
    ): SmsResponse {
        $chunks = array_chunk($phones, 1000);
        $totalSent = 0;
        $totalFailed = 0;
        $totalCost = 0.0;
        $lastRequestId = null;

        foreach ($chunks as $chunk) {
            $request = SmsRequest::bulk(
                recipients: $chunk,
                message: $message,
                type: $type,
                templateSlug: $templateSlug,
                variables: $variables,
            );

            $response = $this->send($request);

            if ($response->success) {
                $totalSent += count($chunk);
                $totalCost += $response->cost;
                $lastRequestId = $response->requestId;
            } else {
                $totalFailed += count($chunk);
            }
        }

        if ($totalFailed === count($phones)) {
            return SmsResponse::failure('All bulk SMS failed');
        }

        return SmsResponse::success(
            message: "Bulk SMS completed: {$totalSent} sent, {$totalFailed} failed",
            requestId: $lastRequestId,
            cost: $totalCost,
            providerData: [
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed,
                'chunks_processed' => count($chunks),
            ],
        );
    }
}

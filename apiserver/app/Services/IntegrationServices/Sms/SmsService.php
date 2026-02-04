<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms;

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Models\Sms\SmsLog;
use App\Services\IntegrationServices\Sms\Contracts\SmsProviderInterface;
use App\Services\IntegrationServices\Sms\DTOs\BalanceInfo;
use App\Services\IntegrationServices\Sms\DTOs\DeliveryReport;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use App\Services\IntegrationServices\Sms\Providers\Fast2SmsProvider;
use App\Services\IntegrationServices\Sms\Providers\LogSmsProvider;
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
     * @var Collection<int, Integration>|null
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
        foreach ($providers as $integration) {
            $driver = $this->createDriver($integration);

            // Create log entry
            $log = $this->createLog($request, $integration);

            try {
                $response = $driver->send($request);

                // Update log with response
                $this->updateLog($log, $response, $integration);

                if ($response->success) {
                    return $response;
                }

                // If insufficient balance, try next provider
                if ($response->errorCode === 'INSUFFICIENT_BALANCE') {
                    Log::warning("SmsService: Provider {$integration->slug} has insufficient balance, trying next");

                    continue;
                }

            } catch (\Throwable $e) {
                Log::error("SmsService: Provider {$integration->slug} failed", [
                    'error' => $e->getMessage(),
                ]);

                $log->markAsFailed($e->getMessage(), 'EXCEPTION');
                $this->recordFailure($integration, $e->getMessage());

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
        $integration = $this->getDefaultProvider();

        if (! $integration) {
            return BalanceInfo::error('No default provider configured');
        }

        return $this->createDriver($integration)->getBalance();
    }

    /**
     * Get balance from all active providers.
     *
     * @return array<string, BalanceInfo>
     */
    public function getAllBalances(): array
    {
        $balances = [];

        foreach ($this->getActiveProviders() as $integration) {
            $balances[$integration->slug] = $this->createDriver($integration)->getBalance();
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

        if (! $log || ! $log->integration_id) {
            return DeliveryReport::error('Log not found', $requestId);
        }

        $integration = Integration::find($log->integration_id);

        if (! $integration) {
            return DeliveryReport::error('Provider not found', $requestId);
        }

        $report = $this->createDriver($integration)->getDeliveryReport($requestId);

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
        $integration = $this->getDefaultProvider();

        if (! $integration) {
            return ['error' => 'No default provider configured'];
        }

        return $this->getMonthlyProjectionForIntegration($integration);
    }

    /**
     * Get all expense projections across providers.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllExpenseProjections(): array
    {
        $projections = [];

        foreach ($this->getActiveProviders() as $integration) {
            $projections[$integration->slug] = $this->getMonthlyProjectionForIntegration($integration);
        }

        return $projections;
    }

    /**
     * Sync balance from provider API.
     */
    public function syncBalance(?string $providerSlug = null): BalanceInfo
    {
        $integration = $providerSlug
            ? Integration::where('slug', $providerSlug)->first()
            : $this->getDefaultProvider();

        if (! $integration) {
            return BalanceInfo::error('Provider not found');
        }

        $driver = $this->createDriver($integration);
        $balance = $driver->getBalance();

        if ($balance->success) {
            $settings = $integration->settings ?? [];
            $settings['balance'] = $balance->balance;
            $settings['balance_checked_at'] = now()->toDateTimeString();
            $integration->settings = $settings;
            $integration->save();
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
    private function getDefaultProvider(): ?Integration
    {
        return $this->getActiveProviders()->first();
    }

    /**
     * Get active providers ordered by priority.
     *
     * @return Collection<int, Integration>
     */
    private function getActiveProviders(): Collection
    {
        if ($this->providers !== null) {
            return $this->providers;
        }

        $integrations = Integration::query()
            ->ofType(IntegrationTypeCast::SMS->value)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        if ($integrations->isNotEmpty()) {
            $this->providers = $integrations
                ->where('is_active', true)
                ->values();

            return $this->providers;
        }

        // If no integrations exist, fall back to config-based provider
        $configIntegration = $this->getConfigBasedIntegration();
        $this->providers = $configIntegration ? collect([$configIntegration]) : collect();

        return $this->providers;
    }

    /**
     * Get serviceable providers (active, healthy, has balance for count).
     *
     * @return Collection<int, Integration>
     */
    private function getServiceableProviders(int $requiredCount = 1): Collection
    {
        $providers = $this->getActiveProviders();

        return $providers->filter(function (Integration $integration) use ($requiredCount) {
            $driver = $this->getDriverSlug($integration);

            // Check circuit breaker
            if ($this->getConsecutiveFailures($integration) >= 5) {
                return false;
            }

            // For log provider in testing, always allow
            if ($driver === 'log') {
                return true;
            }

            // Check balance
            return $this->canIntegrationSend($integration, $requiredCount);
        });
    }

    /**
     * Get best provider for bulk sends.
     */
    private function getBestProviderForBulk(int $count): ?Integration
    {
        $providers = $this->getActiveProviders();

        // Find provider with enough balance and best success rate
        return $providers
            ->filter(fn (Integration $integration) => $this->canIntegrationSend($integration, $count))
            ->sortByDesc(fn (Integration $integration) => $this->getSuccessRate($integration))
            ->first();
    }

    /**
     * Get provider from config (fallback).
     */
    private function getConfigBasedIntegration(): ?Integration
    {
        $providerName = config('services.sms.provider', 'log');

        // In testing, always use log
        if (app()->environment('testing')) {
            $providerName = 'log';
        }

        // Create a virtual provider model from config
        $integration = new Integration([
            'name' => match ($providerName) {
                'fast2sms' => 'Fast2SMS',
                'log' => 'Log Provider',
                default => 'Unknown Provider',
            },
            'slug' => $providerName,
            'type' => IntegrationTypeCast::SMS->value,
            'credentials' => [
                'api_key' => config('services.sms.fast2sms.api_key'),
                'api_secret' => config('services.sms.fast2sms.api_secret'),
                'sender_id' => config('services.sms.fast2sms.sender_id'),
                'entity_id' => config('services.sms.fast2sms.entity_id'),
            ],
            'settings' => [
                'driver' => $providerName,
                'per_sms_cost' => (float) config('services.sms.fast2sms.per_sms_cost', 0.25),
                'min_balance_threshold' => (float) config('services.sms.fast2sms.min_balance_threshold', 10.0),
                'balance' => 999999.99,
                'priority' => 1,
                'success_rate' => 100.0,
                'consecutive_failures' => 0,
            ],
            'is_active' => true,
            'is_default' => true,
        ]);

        $integration->exists = false;

        return $integration;
    }

    // =========================================================================
    // LOGGING
    // =========================================================================

    /**
     * Create driver instance for an integration.
     */
    private function createDriver(Integration $integration): SmsProviderInterface
    {
        $driver = $this->getDriverSlug($integration);
        $settings = $integration->settings ?? [];

        $provider = match ($driver) {
            'fast2sms' => new Fast2SmsProvider(
                apiKey: $integration->getApiKey(),
                senderId: $settings['sender_id'] ?? $integration->getCredential('sender_id'),
                entityId: $settings['entity_id'] ?? $integration->getCredential('entity_id'),
                perSmsCost: (float) ($settings['per_sms_cost'] ?? config('services.sms.fast2sms.per_sms_cost', 0.25)),
                minBalanceThreshold: (float) ($settings['min_balance_threshold'] ?? config('services.sms.fast2sms.min_balance_threshold', 10.0)),
            ),
            default => new LogSmsProvider,
        };

        $provider->setIntegration($integration);

        return $provider;
    }

    private function getDriverSlug(Integration $integration): string
    {
        $settings = $integration->settings ?? [];

        return $settings['driver']
            ?? $settings['provider']
            ?? $integration->slug
            ?? 'log';
    }

    private function getSetting(Integration $integration, string $key, mixed $default = null): mixed
    {
        $settings = $integration->settings ?? [];

        return $settings[$key] ?? $default;
    }

    private function setSetting(Integration $integration, string $key, mixed $value): void
    {
        $settings = $integration->settings ?? [];
        $settings[$key] = $value;
        $integration->settings = $settings;
        $integration->save();
    }

    private function getBalanceForIntegration(Integration $integration): float
    {
        return (float) $this->getSetting($integration, 'balance', 0.0);
    }

    private function getPerSmsCostForIntegration(Integration $integration): float
    {
        return (float) $this->getSetting(
            $integration,
            'per_sms_cost',
            config('services.sms.fast2sms.per_sms_cost', 0.25)
        );
    }

    private function getMinBalanceThresholdForIntegration(Integration $integration): float
    {
        return (float) $this->getSetting(
            $integration,
            'min_balance_threshold',
            config('services.sms.fast2sms.min_balance_threshold', 10.0)
        );
    }

    private function getSuccessRate(Integration $integration): float
    {
        return (float) $this->getSetting($integration, 'success_rate', 100.0);
    }

    private function getConsecutiveFailures(Integration $integration): int
    {
        return (int) $this->getSetting($integration, 'consecutive_failures', 0);
    }

    private function canIntegrationSend(Integration $integration, int $count = 1): bool
    {
        $perSmsCost = $this->getPerSmsCostForIntegration($integration);
        $required = $count * $perSmsCost;

        return $this->getBalanceForIntegration($integration) >= $required
            && $this->getBalanceForIntegration($integration) >= $this->getMinBalanceThresholdForIntegration($integration);
    }

    private function recordSuccess(Integration $integration, int $count = 1, float $cost = 0): void
    {
        $totalSent = (int) $this->getSetting($integration, 'total_sent', 0) + $count;
        $totalDelivered = (int) $this->getSetting($integration, 'total_delivered', 0) + $count;
        $balance = max(0, $this->getBalanceForIntegration($integration) - $cost);

        $this->setSetting($integration, 'total_sent', $totalSent);
        $this->setSetting($integration, 'total_delivered', $totalDelivered);
        $this->setSetting($integration, 'balance', $balance);
        $this->setSetting($integration, 'last_success_at', now()->toDateTimeString());
        $this->setSetting($integration, 'consecutive_failures', 0);

        $this->updateSuccessRate($integration);
    }

    private function recordFailure(Integration $integration, string $error, int $count = 1): void
    {
        $totalSent = (int) $this->getSetting($integration, 'total_sent', 0) + $count;
        $totalFailed = (int) $this->getSetting($integration, 'total_failed', 0) + $count;
        $consecutive = $this->getConsecutiveFailures($integration) + 1;

        $this->setSetting($integration, 'total_sent', $totalSent);
        $this->setSetting($integration, 'total_failed', $totalFailed);
        $this->setSetting($integration, 'consecutive_failures', $consecutive);
        $this->setSetting($integration, 'last_failure_at', now()->toDateTimeString());
        $this->setSetting($integration, 'last_error', $error);

        $this->updateSuccessRate($integration);
    }

    private function updateSuccessRate(Integration $integration): void
    {
        $totalSent = (int) $this->getSetting($integration, 'total_sent', 0);
        $totalDelivered = (int) $this->getSetting($integration, 'total_delivered', 0);

        if ($totalSent <= 0) {
            $this->setSetting($integration, 'success_rate', 0.0);

            return;
        }

        $rate = ($totalDelivered / $totalSent) * 100;
        $this->setSetting($integration, 'success_rate', round($rate, 2));
    }

    /**
     * Get monthly expense projection for an integration.
     *
     * @return array<string, mixed>
     */
    private function getMonthlyProjectionForIntegration(Integration $integration): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $daysInMonth = $now->daysInMonth;
        $daysPassed = $now->day;
        $daysRemaining = $daysInMonth - $daysPassed;

        $monthlyStats = SmsLog::query()
            ->where('integration_id', $integration->id)
            ->where('created_at', '>=', $startOfMonth)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(cost) as total_cost')
            ->selectRaw('COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered_count')
            ->first();

        $totalSentThisMonth = $monthlyStats->total_count ?? 0;
        $totalCostThisMonth = (float) ($monthlyStats->total_cost ?? 0);
        $deliveredThisMonth = $monthlyStats->delivered_count ?? 0;

        $dailyAverage = $daysPassed > 0 ? $totalSentThisMonth / $daysPassed : 0;
        $dailyCostAverage = $daysPassed > 0 ? $totalCostThisMonth / $daysPassed : 0;

        $projectedMonthlyCount = (int) round($dailyAverage * $daysInMonth);
        $projectedMonthlyCost = $dailyCostAverage * $daysInMonth;
        $projectedRemainingCost = $dailyCostAverage * $daysRemaining;

        $balance = $this->getBalanceForIntegration($integration);
        $daysBalanceWillLast = $dailyCostAverage > 0 ? (int) floor($balance / $dailyCostAverage) : 999;
        $balanceRunOutDate = $dailyCostAverage > 0 ? $now->copy()->addDays($daysBalanceWillLast) : null;
        $recommendedRecharge = max(0, $projectedRemainingCost - $balance);

        return [
            'period' => [
                'month' => $now->format('F Y'),
                'days_passed' => $daysPassed,
                'days_remaining' => $daysRemaining,
                'days_in_month' => $daysInMonth,
            ],
            'actual' => [
                'sms_sent' => $totalSentThisMonth,
                'sms_delivered' => $deliveredThisMonth,
                'total_cost' => round($totalCostThisMonth, 2),
                'daily_average_count' => round($dailyAverage, 1),
                'daily_average_cost' => round($dailyCostAverage, 2),
            ],
            'projected' => [
                'monthly_sms_count' => $projectedMonthlyCount,
                'monthly_cost' => round($projectedMonthlyCost, 2),
                'remaining_cost' => round($projectedRemainingCost, 2),
            ],
            'balance' => [
                'current' => round($balance, 2),
                'can_send_count' => $this->getPerSmsCostForIntegration($integration) > 0
                    ? (int) floor($balance / $this->getPerSmsCostForIntegration($integration))
                    : 0,
                'days_will_last' => $daysBalanceWillLast,
                'run_out_date' => $balanceRunOutDate?->format('Y-m-d'),
                'is_sufficient_for_month' => $balance >= $projectedRemainingCost,
            ],
            'recharge' => [
                'recommended_amount' => round($recommendedRecharge, 2),
                'current_per_sms_cost' => $this->getPerSmsCostForIntegration($integration),
            ],
            'health' => [
                'success_rate' => $this->getSuccessRate($integration),
                'consecutive_failures' => $this->getConsecutiveFailures($integration),
            ],
        ];
    }

    /**
     * Create log entry for SMS request.
     */
    private function createLog(SmsRequest $request, Integration $integration): SmsLog
    {
        return SmsLog::create([
            'integration_id' => $integration->id,
            'provider_slug' => $integration->slug,
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
    private function updateLog(SmsLog $log, SmsResponse $response, Integration $integration): void
    {
        if ($response->success) {
            $log->markAsSent($response->requestId, $response->messageId);
            $log->update(['cost' => $response->cost]);
            $this->recordSuccess($integration, 1, $response->cost);
        } else {
            $log->markAsFailed($response->errorMessage ?? $response->message, $response->errorCode);
            $this->recordFailure($integration, $response->message);
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

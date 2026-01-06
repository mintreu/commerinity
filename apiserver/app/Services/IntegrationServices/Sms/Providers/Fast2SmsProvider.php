<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\Providers;

use App\Models\Sms\SmsProvider;
use App\Services\IntegrationServices\Sms\Contracts\SmsProviderInterface;
use App\Services\IntegrationServices\Sms\DTOs\BalanceInfo;
use App\Services\IntegrationServices\Sms\DTOs\DeliveryReport;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fast2SMS Provider - Complete implementation for Fast2SMS India.
 *
 * Supports:
 * - DLT SMS (primary route for production)
 * - Quick SMS (for testing without DLT)
 * - Wallet balance checking
 * - Delivery reports
 * - SMS logs/analytics
 *
 * @see https://docs.fast2sms.com/
 */
final class Fast2SmsProvider implements SmsProviderInterface
{
    private const BASE_URL = 'https://www.fast2sms.com/dev';

    private const BULK_URL = self::BASE_URL.'/bulkV2';

    private const WALLET_URL = self::BASE_URL.'/wallet';

    private const LOGS_URL = self::BASE_URL.'/sms_logs';

    private const DELIVERY_URL = self::BASE_URL.'/delivery';

    private const ROUTE_DLT = 'dlt';

    private const ROUTE_DLT_MANUAL = 'dlt_manual';

    private const ROUTE_QUICK = 'q';

    private const BALANCE_CACHE_TTL = 300; // 5 minutes

    private ?SmsProvider $providerModel = null;

    private ?BalanceInfo $cachedBalance = null;

    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $senderId = null,
        private readonly ?string $entityId = null,
        private readonly float $perSmsCost = 0.25,
        private readonly float $minBalanceThreshold = 10.0,
    ) {}

    public function getSlug(): string
    {
        return 'fast2sms';
    }

    public function getName(): string
    {
        return 'Fast2SMS';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->getApiKey());
    }

    public function isServiceable(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        // Check if service is healthy (balance check works)
        $balance = $this->getBalance();

        return $balance->success && $balance->canSend(1);
    }

    public function send(SmsRequest $request): SmsResponse
    {
        if (! $this->isConfigured()) {
            return SmsResponse::failure('Fast2SMS not configured');
        }

        // Check balance before sending (especially for bulk)
        $recipientCount = $request->getRecipientCount();
        $balance = $this->getBalance();

        if (! $balance->canSend($recipientCount)) {
            $this->logBalanceWarning($balance, $recipientCount);

            return SmsResponse::insufficientBalance(
                $balance->balance,
                $balance->getRequiredBalance($recipientCount)
            );
        }

        // Route based on template usage
        if ($request->usesTemplate()) {
            return $this->sendDltFromRequest($request);
        }

        return $this->sendQuickSms($request);
    }

    public function sendLegacy(array $recipients, string $message): array
    {
        $request = new SmsRequest(
            recipients: $recipients,
            message: $message,
            type: 'transactional',
        );

        $response = $this->send($request);

        return [
            'success' => $response->success,
            'message' => $response->message,
            'data' => $response->success ? [
                'request_id' => $response->requestId,
                'recipients' => $recipients,
            ] : null,
        ];
    }

    public function sendDlt(
        array $recipients,
        string $messageId,
        string $variablesValues,
        ?string $senderId = null,
    ): SmsResponse {
        if (! $this->isConfigured()) {
            return SmsResponse::failure('Fast2SMS not configured');
        }

        try {
            $numbers = $this->formatPhoneNumbers($recipients);

            $response = $this->httpClient()->post(self::BULK_URL, [
                'route' => self::ROUTE_DLT,
                'sender_id' => $senderId ?? $this->getSenderId(),
                'message' => $messageId,
                'variables_values' => $variablesValues,
                'numbers' => implode(',', $numbers),
            ]);

            return $this->parseResponse($response->json(), $recipients);

        } catch (\Throwable $e) {
            $this->logError('DLT SMS failed', $e, $recipients);

            return SmsResponse::failure('SMS service error: '.$e->getMessage());
        }
    }

    public function getBalance(): BalanceInfo
    {
        // Return cached balance if fresh (within TTL)
        $cacheKey = "sms:balance:fast2sms:{$this->getApiKeyHash()}";

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        if (! $this->isConfigured()) {
            return BalanceInfo::error('Fast2SMS not configured');
        }

        try {
            $response = Http::withHeaders([
                'authorization' => $this->getApiKey(),
            ])->get(self::WALLET_URL);

            $data = $response->json();

            if ($response->successful() && ($data['return'] ?? false)) {
                $balance = BalanceInfo::fromBalance(
                    balance: (float) ($data['wallet'] ?? 0),
                    perSmsCost: $this->getPerSmsCost(),
                    threshold: $this->getMinBalanceThreshold(),
                );

                // Cache the balance
                Cache::put($cacheKey, $balance, self::BALANCE_CACHE_TTL);

                // Update provider model if available
                $this->updateProviderBalance($balance);

                return $balance;
            }

            return BalanceInfo::error($data['message'] ?? 'Failed to fetch balance');

        } catch (\Throwable $e) {
            Log::error('Fast2SMS balance check failed', [
                'error' => $e->getMessage(),
            ]);

            return BalanceInfo::error('Balance check failed: '.$e->getMessage());
        }
    }

    public function canSend(int $count = 1): bool
    {
        return $this->getBalance()->canSend($count);
    }

    public function getDeliveryReport(string $requestId): DeliveryReport
    {
        if (! $this->isConfigured()) {
            return DeliveryReport::error('Fast2SMS not configured', $requestId);
        }

        try {
            $response = Http::withHeaders([
                'authorization' => $this->getApiKey(),
            ])->get(self::DELIVERY_URL, [
                'request_id' => $requestId,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['return'] ?? false)) {
                // Fast2SMS returns array of delivery reports
                $reports = $data['data'] ?? [];
                if (! empty($reports)) {
                    return DeliveryReport::fromFast2Sms($reports[0]);
                }
            }

            return DeliveryReport::error(
                $data['message'] ?? 'Delivery report not found',
                $requestId
            );

        } catch (\Throwable $e) {
            Log::error('Fast2SMS delivery report failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);

            return DeliveryReport::error($e->getMessage(), $requestId);
        }
    }

    public function getDeliveryReports(array $requestIds): array
    {
        $reports = [];

        foreach ($requestIds as $requestId) {
            $reports[$requestId] = $this->getDeliveryReport($requestId);
        }

        return $reports;
    }

    public function getLogs(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Fast2SMS not configured'];
        }

        try {
            // Fast2SMS returns last 3 days of logs
            $response = Http::withHeaders([
                'authorization' => $this->getApiKey(),
            ])->get(self::LOGS_URL);

            $data = $response->json();

            if ($response->successful() && ($data['return'] ?? false)) {
                return [
                    'success' => true,
                    'logs' => $data['data'] ?? [],
                    'total' => $data['total'] ?? count($data['data'] ?? []),
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to fetch logs',
            ];

        } catch (\Throwable $e) {
            Log::error('Fast2SMS logs fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function setProviderModel(SmsProvider $provider): void
    {
        $this->providerModel = $provider;
    }

    public function getProviderModel(): ?SmsProvider
    {
        return $this->providerModel;
    }

    /**
     * Send DLT SMS from SmsRequest DTO.
     */
    private function sendDltFromRequest(SmsRequest $request): SmsResponse
    {
        // Get template from database if slug provided
        $template = $this->resolveTemplate($request->templateSlug);

        if (! $template) {
            return SmsResponse::failure("Template not found: {$request->templateSlug}");
        }

        return $this->sendDlt(
            recipients: $request->recipients,
            messageId: $template['message_id'],
            variablesValues: $request->getVariablesAsPipeString() ?: $request->message,
            senderId: $template['sender_id'] ?? null,
        );
    }

    /**
     * Send Quick SMS (non-DLT, for testing).
     */
    private function sendQuickSms(SmsRequest $request): SmsResponse
    {
        try {
            $numbers = $this->formatPhoneNumbers($request->recipients);

            $response = $this->httpClient()->post(self::BULK_URL, [
                'route' => self::ROUTE_QUICK,
                'message' => $request->message,
                'language' => 'english',
                'flash' => 0,
                'numbers' => implode(',', $numbers),
            ]);

            return $this->parseResponse($response->json(), $request->recipients);

        } catch (\Throwable $e) {
            $this->logError('Quick SMS failed', $e, $request->recipients);

            return SmsResponse::failure('SMS service error: '.$e->getMessage());
        }
    }

    /**
     * Parse Fast2SMS API response.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string>  $recipients
     */
    private function parseResponse(array $data, array $recipients): SmsResponse
    {
        if ($data['return'] ?? false) {
            $cost = count($recipients) * $this->getPerSmsCost();

            // Invalidate balance cache after sending
            $this->invalidateBalanceCache();

            return SmsResponse::success(
                message: 'SMS sent successfully',
                requestId: $data['request_id'] ?? null,
                messageId: $data['message'][0] ?? null,
                cost: $cost,
                providerData: $data,
            );
        }

        Log::error('Fast2SMS API error', [
            'response' => $data,
            'recipients' => count($recipients),
        ]);

        return SmsResponse::failure(
            message: $data['message'] ?? 'SMS sending failed',
            errorCode: (string) ($data['status_code'] ?? 'UNKNOWN'),
            errorMessage: $data['message'] ?? null,
            providerData: $data,
        );
    }

    /**
     * Get configured HTTP client.
     */
    private function httpClient(): PendingRequest
    {
        return Http::withHeaders([
            'authorization' => $this->getApiKey(),
            'Content-Type' => 'application/json',
        ])->timeout(30);
    }

    /**
     * Format phone numbers (remove +91 prefix).
     *
     * @param  array<string>  $phones
     * @return array<string>
     */
    private function formatPhoneNumbers(array $phones): array
    {
        return array_map(function (string $phone): string {
            // Remove +91 prefix and any non-digit characters
            $cleaned = preg_replace('/[^\d]/', '', $phone);

            // Remove leading 91 if present (Indian country code)
            if (strlen($cleaned ?? '') > 10 && str_starts_with($cleaned ?? '', '91')) {
                return substr($cleaned ?? '', 2);
            }

            return $cleaned ?? $phone;
        }, $phones);
    }

    /**
     * Resolve template from database.
     *
     * @return array<string, mixed>|null
     */
    private function resolveTemplate(?string $slug): ?array
    {
        if (! $slug || ! $this->providerModel) {
            // Return mock template for testing
            return [
                'message_id' => '123456', // Demo message ID
                'sender_id' => $this->getSenderId(),
            ];
        }

        $template = $this->providerModel->templates()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return null;
        }

        // Update usage stats
        $template->increment('usage_count');
        $template->update(['last_used_at' => now()]);

        return [
            'message_id' => $template->message_id,
            'sender_id' => $template->sender_id,
            'entity_id' => $template->entity_id,
        ];
    }

    /**
     * Update provider model balance.
     */
    private function updateProviderBalance(BalanceInfo $balance): void
    {
        if ($this->providerModel) {
            $this->providerModel->update([
                'balance' => $balance->balance,
                'balance_checked_at' => now(),
            ]);
        }
    }

    /**
     * Invalidate balance cache after sending.
     */
    private function invalidateBalanceCache(): void
    {
        $cacheKey = "sms:balance:fast2sms:{$this->getApiKeyHash()}";
        Cache::forget($cacheKey);
    }

    /**
     * Get API key hash for cache key.
     */
    private function getApiKeyHash(): string
    {
        return md5($this->getApiKey() ?? 'none');
    }

    /**
     * Log error with context.
     *
     * @param  array<string>  $recipients
     */
    private function logError(string $message, \Throwable $e, array $recipients): void
    {
        Log::error("Fast2SMS: {$message}", [
            'error' => $e->getMessage(),
            'recipients' => count($recipients),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Log balance warning.
     */
    private function logBalanceWarning(BalanceInfo $balance, int $count): void
    {
        Log::warning('Fast2SMS: Insufficient balance', [
            'current_balance' => $balance->balance,
            'required_count' => $count,
            'required_amount' => $balance->getRequiredBalance($count),
            'per_sms_cost' => $balance->perSmsCost,
        ]);
    }

    /**
     * Get API key from model or constructor.
     */
    private function getApiKey(): ?string
    {
        return $this->providerModel?->api_key ?? $this->apiKey;
    }

    /**
     * Get sender ID from model or constructor.
     */
    private function getSenderId(): ?string
    {
        return $this->providerModel?->sender_id ?? $this->senderId;
    }

    /**
     * Get entity ID from model or constructor.
     */
    private function getEntityId(): ?string
    {
        return $this->providerModel?->entity_id ?? $this->entityId;
    }

    /**
     * Get per SMS cost from model or constructor.
     */
    private function getPerSmsCost(): float
    {
        return $this->providerModel?->per_sms_cost ?? $this->perSmsCost;
    }

    /**
     * Get minimum balance threshold from model or constructor.
     */
    private function getMinBalanceThreshold(): float
    {
        return $this->providerModel?->min_balance_threshold ?? $this->minBalanceThreshold;
    }
}

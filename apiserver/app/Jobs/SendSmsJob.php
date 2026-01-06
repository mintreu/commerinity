<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Sms\SmsLog;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use App\Services\IntegrationServices\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job for sending SMS asynchronously.
 *
 * Handles:
 * - Single SMS
 * - Bulk SMS
 * - OTP SMS (with higher priority)
 * - Automatic retries on failure
 */
class SendSmsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly SmsRequest $request,
        public readonly ?int $logId = null,
    ) {
        // Set queue based on message type
        $this->onQueue($this->getQueueName());
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        Log::info('SendSmsJob: Processing SMS', [
            'recipient' => $this->request->getRecipient(),
            'type' => $this->request->type,
            'attempt' => $this->attempts(),
        ]);

        try {
            $response = $smsService->send($this->request);

            // If we have a log ID, update it
            if ($this->logId) {
                $this->updateExistingLog($response);
            }

            if (! $response->success) {
                // Check if we should retry
                if ($this->shouldRetry($response)) {
                    $this->release($this->getRetryDelay());

                    return;
                }

                Log::error('SendSmsJob: SMS failed', [
                    'recipient' => $this->request->getRecipient(),
                    'error' => $response->message,
                    'attempts' => $this->attempts(),
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('SendSmsJob: Exception', [
                'recipient' => $this->request->getRecipient(),
                'error' => $e->getMessage(),
                'attempts' => $this->attempts(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendSmsJob: Job failed permanently', [
            'recipient' => $this->request->getRecipient(),
            'type' => $this->request->type,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update log if exists
        if ($this->logId) {
            $log = SmsLog::find($this->logId);
            $log?->markAsFailed($exception->getMessage(), 'JOB_FAILED');
        }
    }

    /**
     * Get the queue name based on message type.
     */
    private function getQueueName(): string
    {
        // OTPs get high priority queue
        if ($this->request->type === 'otp') {
            return config('services.sms.options.queue_name', 'notifications').'-high';
        }

        return config('services.sms.options.queue_name', 'notifications');
    }

    /**
     * Get retry delay based on attempt number.
     */
    private function getRetryDelay(): int
    {
        $baseDelay = (int) config('services.sms.options.retry_delay', 60);

        // Exponential backoff: 60s, 120s, 240s
        return $baseDelay * pow(2, $this->attempts() - 1);
    }

    /**
     * Check if should retry based on response.
     */
    private function shouldRetry(SmsResponse $response): bool
    {
        // Don't retry if max attempts reached
        if ($this->attempts() >= $this->tries) {
            return false;
        }

        // Don't retry certain error types
        $nonRetryableErrors = [
            'INVALID_NUMBER',
            'BLOCKED_NUMBER',
            'INSUFFICIENT_BALANCE', // Provider issue, not transient
        ];

        if (in_array($response->errorCode, $nonRetryableErrors, true)) {
            return false;
        }

        return true;
    }

    /**
     * Update existing log entry.
     */
    private function updateExistingLog(SmsResponse $response): void
    {
        $log = SmsLog::find($this->logId);

        if (! $log) {
            return;
        }

        if ($response->success) {
            $log->markAsSent($response->requestId, $response->messageId);
            $log->update(['cost' => $response->cost]);
        } else {
            $log->markAsFailed($response->errorMessage ?? $response->message, $response->errorCode);
        }
    }

    /**
     * Create and dispatch job for OTP.
     */
    public static function dispatchOtp(string $phone, string $otp, ?int $userId = null): self
    {
        $request = SmsRequest::otp($phone, $otp, $userId ? \App\Models\User::find($userId) : null);

        return dispatch(new self($request));
    }

    /**
     * Create and dispatch job for transactional SMS.
     */
    public static function dispatchTransactional(string $phone, string $message, ?int $userId = null): self
    {
        $request = SmsRequest::single(
            recipient: $phone,
            message: $message,
            type: 'transactional',
            user: $userId ? \App\Models\User::find($userId) : null,
        );

        return dispatch(new self($request));
    }

    /**
     * Create and dispatch job for bulk SMS.
     *
     * @param  array<string>  $phones
     */
    public static function dispatchBulk(array $phones, string $message, ?string $templateSlug = null): self
    {
        $request = SmsRequest::bulk(
            recipients: $phones,
            message: $message,
            type: 'promotional',
            templateSlug: $templateSlug,
        );

        return dispatch(new self($request));
    }
}

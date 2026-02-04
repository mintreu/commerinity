<?php

declare(strict_types=1);

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Models\Sms\SmsLog;
use App\Models\Sms\SmsTemplate;
use App\Services\IntegrationServices\Sms\DTOs\BalanceInfo;
use App\Services\IntegrationServices\Sms\DTOs\DeliveryReport;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use App\Services\IntegrationServices\Sms\Providers\LogSmsProvider;
use App\Services\IntegrationServices\Sms\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// =============================================================================
// SMS REQUEST DTO TESTS
// =============================================================================

describe('SmsRequest DTO', function () {
    it('creates single recipient request', function () {
        $request = SmsRequest::single(
            recipient: '+919876543210',
            message: 'Test message',
            type: 'transactional',
        );

        expect($request->recipients)->toBe(['+919876543210'])
            ->and($request->message)->toBe('Test message')
            ->and($request->type)->toBe('transactional')
            ->and($request->getRecipientCount())->toBe(1)
            ->and($request->isBulk())->toBeFalse();
    });

    it('creates OTP request', function () {
        $request = SmsRequest::otp('+919876543210', '123456');

        expect($request->type)->toBe('otp')
            ->and($request->templateSlug)->toBe('otp-verification')
            ->and($request->variables)->toBe(['otp' => '123456'])
            ->and($request->usesTemplate())->toBeTrue();
    });

    it('creates bulk request', function () {
        $phones = ['+919876543210', '+919876543211', '+919876543212'];
        $request = SmsRequest::bulk($phones, 'Bulk message', 'promotional');

        expect($request->getRecipientCount())->toBe(3)
            ->and($request->isBulk())->toBeTrue()
            ->and($request->type)->toBe('promotional');
    });

    it('converts variables to pipe string', function () {
        $request = new SmsRequest(
            recipients: ['+919876543210'],
            message: 'Test',
            variables: ['otp' => '123456', 'name' => 'John'],
        );

        expect($request->getVariablesAsPipeString())->toBe('123456|John');
    });
});

// =============================================================================
// SMS RESPONSE DTO TESTS
// =============================================================================

describe('SmsResponse DTO', function () {
    it('creates success response', function () {
        $response = SmsResponse::success(
            message: 'SMS sent',
            requestId: 'REQ123',
            messageId: 'MSG123',
            cost: 0.25,
        );

        expect($response->success)->toBeTrue()
            ->and($response->message)->toBe('SMS sent')
            ->and($response->requestId)->toBe('REQ123')
            ->and($response->status)->toBe('sent')
            ->and($response->cost)->toBe(0.25);
    });

    it('creates failure response', function () {
        $response = SmsResponse::failure(
            message: 'SMS failed',
            errorCode: 'ERR001',
        );

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe('failed')
            ->and($response->errorCode)->toBe('ERR001');
    });

    it('creates insufficient balance response', function () {
        $response = SmsResponse::insufficientBalance(5.0, 10.0);

        expect($response->success)->toBeFalse()
            ->and($response->errorCode)->toBe('INSUFFICIENT_BALANCE')
            ->and($response->message)->toContain('5')
            ->and($response->message)->toContain('10');
    });
});

// =============================================================================
// BALANCE INFO DTO TESTS
// =============================================================================

describe('BalanceInfo DTO', function () {
    it('creates from balance', function () {
        $balance = BalanceInfo::fromBalance(
            balance: 100.0,
            perSmsCost: 0.25,
            threshold: 10.0,
        );

        expect($balance->success)->toBeTrue()
            ->and($balance->balance)->toBe(100.0)
            ->and($balance->canSendCount)->toBe(400)
            ->and($balance->isLow)->toBeFalse();
    });

    it('detects low balance', function () {
        $balance = BalanceInfo::fromBalance(
            balance: 5.0,
            perSmsCost: 0.25,
            threshold: 10.0,
        );

        expect($balance->isLow)->toBeTrue();
    });

    it('calculates if can send', function () {
        $balance = BalanceInfo::fromBalance(balance: 1.0, perSmsCost: 0.25);

        expect($balance->canSend(4))->toBeTrue()
            ->and($balance->canSend(5))->toBeFalse();
    });

    it('calculates required balance', function () {
        $balance = BalanceInfo::fromBalance(balance: 100.0, perSmsCost: 0.25);

        expect($balance->getRequiredBalance(10))->toBe(2.5);
    });
});

// =============================================================================
// LOG SMS PROVIDER TESTS
// =============================================================================

describe('LogSmsProvider', function () {
    it('is always configured and serviceable', function () {
        $provider = new LogSmsProvider;

        expect($provider->isConfigured())->toBeTrue()
            ->and($provider->isServiceable())->toBeTrue()
            ->and($provider->getSlug())->toBe('log')
            ->and($provider->getName())->toBe('Log Provider (Testing)');
    });

    it('sends SMS successfully', function () {
        $provider = new LogSmsProvider;
        $request = SmsRequest::single('+919876543210', 'Test message');

        $response = $provider->send($request);

        expect($response->success)->toBeTrue()
            ->and($response->requestId)->toStartWith('LOG_');
    });

    it('reports unlimited balance', function () {
        $provider = new LogSmsProvider;

        $balance = $provider->getBalance();

        expect($balance->success)->toBeTrue()
            ->and($balance->balance)->toBe(999999.99);
        // Log provider has perSmsCost = 0, so canSendCount is 0
        // But canSend() method still returns true
    });

    it('always can send', function () {
        $provider = new LogSmsProvider;

        expect($provider->canSend(1))->toBeTrue()
            ->and($provider->canSend(1000000))->toBeTrue();
    });

    it('reports delivered for delivery reports', function () {
        $provider = new LogSmsProvider;

        $report = $provider->getDeliveryReport('REQ123');

        expect($report->success)->toBeTrue()
            ->and($report->status)->toBe(DeliveryReport::STATUS_DELIVERED);
    });
});

// =============================================================================
// SMS PROVIDER MODEL TESTS
// =============================================================================

// =============================================================================
// SMS LOG MODEL TESTS
// =============================================================================

describe('SmsLog Model', function () {
    it('creates log entry', function () {
        $log = SmsLog::create([
            'provider_slug' => 'log',
            'recipient' => '+919876543210',
            'message' => 'Test message',
            'message_type' => 'transactional',
            'status' => SmsLog::STATUS_PENDING,
        ]);

        expect($log->uuid)->not->toBeNull()
            ->and($log->status)->toBe('pending');
    });

    it('marks as sent', function () {
        $log = SmsLog::create([
            'provider_slug' => 'log',
            'recipient' => '+919876543210',
            'message' => 'Test',
            'status' => SmsLog::STATUS_PENDING,
        ]);

        $log->markAsSent('REQ123', 'MSG123');

        expect($log->status)->toBe(SmsLog::STATUS_SENT)
            ->and($log->request_id)->toBe('REQ123')
            ->and($log->sent_at)->not->toBeNull();
    });

    it('marks as delivered', function () {
        $log = SmsLog::create([
            'provider_slug' => 'log',
            'recipient' => '+919876543210',
            'message' => 'Test',
            'status' => SmsLog::STATUS_SENT,
        ]);

        $log->markAsDelivered('DELIVERED');

        expect($log->status)->toBe(SmsLog::STATUS_DELIVERED)
            ->and($log->delivered_at)->not->toBeNull();
    });

    it('marks as failed', function () {
        $log = SmsLog::create([
            'provider_slug' => 'log',
            'recipient' => '+919876543210',
            'message' => 'Test',
            'status' => SmsLog::STATUS_PENDING,
        ]);

        $log->markAsFailed('Network error', 'NET_ERR');

        expect($log->status)->toBe(SmsLog::STATUS_FAILED)
            ->and($log->error_code)->toBe('NET_ERR')
            ->and($log->error_message)->toBe('Network error');
    });

    it('checks if retryable', function () {
        $log = SmsLog::create([
            'provider_slug' => 'log',
            'recipient' => '+919876543210',
            'message' => 'Test',
            'status' => SmsLog::STATUS_FAILED,
            'retry_count' => 1,
            'max_retries' => 3,
        ]);

        expect($log->canRetry())->toBeTrue();

        $log->update(['retry_count' => 3]);

        expect($log->canRetry())->toBeFalse();
    });

    it('gets analytics', function () {
        // Create some logs
        SmsLog::create([
            'provider_slug' => 'fast2sms',
            'recipient' => '+919876543210',
            'message' => 'Test 1',
            'message_type' => 'otp',
            'status' => SmsLog::STATUS_DELIVERED,
            'cost' => 0.25,
        ]);

        SmsLog::create([
            'provider_slug' => 'fast2sms',
            'recipient' => '+919876543211',
            'message' => 'Test 2',
            'message_type' => 'transactional',
            'status' => SmsLog::STATUS_FAILED,
            'cost' => 0.25,
        ]);

        $analytics = SmsLog::getAnalytics(now()->subDay(), now());

        expect($analytics['totals']['sent'])->toBe(2)
            ->and($analytics['totals']['delivered'])->toBe(1)
            ->and($analytics['totals']['failed'])->toBe(1)
            ->and($analytics['rates']['delivery_rate'])->toBe(50.0)
            ->and($analytics['by_type'])->toHaveKey('otp');
    });
});

// =============================================================================
// SMS SERVICE TESTS
// =============================================================================

describe('SmsService', function () {
    it('sends SMS using log provider in testing', function () {
        $service = new SmsService;
        $request = SmsRequest::single('+919876543210', 'Test message');

        $response = $service->send($request);

        expect($response->success)->toBeTrue();
    });

    it('sends OTP', function () {
        $service = new SmsService;

        $response = $service->sendOtp('+919876543210', '123456');

        expect($response->success)->toBeTrue();
    });

    it('sends single SMS', function () {
        $service = new SmsService;

        $response = $service->sendSingle('+919876543210', 'Hello World');

        expect($response->success)->toBeTrue();
    });

    it('gets balance', function () {
        $service = new SmsService;

        $balance = $service->getBalance();

        // In testing, uses log provider which has unlimited balance
        expect($balance->success)->toBeTrue()
            ->and($balance->balance)->toBe(999999.99);
    });

    it('checks if can send', function () {
        $service = new SmsService;

        expect($service->canSend(1))->toBeTrue()
            ->and($service->canSend(1000))->toBeTrue();
    });

    it('gets analytics', function () {
        $service = new SmsService;

        $analytics = $service->getAnalytics();

        expect($analytics)->toHaveKeys(['period', 'totals', 'rates', 'cost']);
    });

    it('uses database provider when available', function () {
        Integration::create([
            'name' => 'SMS Log Provider',
            'slug' => 'sms-log',
            'type' => IntegrationTypeCast::SMS,
            'credentials' => [],
            'settings' => [
                'driver' => 'log',
            ],
            'is_sandbox' => false,
            'is_active' => true,
            'is_default' => true,
        ]);

        $service = new SmsService;

        expect($service->getActiveProviderSlug())->toBe('sms-log');
    });

    it('falls back to config provider when no DB providers', function () {
        Integration::query()->where('type', IntegrationTypeCast::SMS->value)->delete();

        $service = new SmsService;

        // In testing environment, falls back to log
        expect($service->getActiveProviderSlug())->toBe('log');
    });
});

// =============================================================================
// SMS TEMPLATE MODEL TESTS
// =============================================================================

describe('SmsTemplate Model', function () {
    it('creates template', function () {
        $integration = Integration::create([
            'name' => 'SMS Log Provider',
            'slug' => 'sms-log',
            'type' => IntegrationTypeCast::SMS,
            'credentials' => [],
            'settings' => [
                'driver' => 'log',
            ],
            'is_sandbox' => false,
            'is_active' => true,
            'is_default' => true,
        ]);

        $template = SmsTemplate::create([
            'integration_id' => $integration->id,
            'name' => 'OTP Template',
            'slug' => 'otp-verification',
            'message_id' => '123456',
            'sender_id' => 'TESTSMS',
            'content' => 'Your OTP is {#var1#}. Valid for 10 minutes.',
            'variables' => ['var1'],
            'variable_count' => 1,
            'category' => 'otp',
            'is_active' => true,
        ]);

        expect($template->id)->toBeGreaterThan(0)
            ->and($template->integration->id)->toBe($integration->id);
    });

    it('renders template with variables', function () {
        $template = new SmsTemplate([
            'content' => 'Hello {#name#}, your OTP is {#otp#}.',
        ]);

        $rendered = $template->render(['name' => 'John', 'otp' => '123456']);

        expect($rendered)->toBe('Hello John, your OTP is 123456.');
    });

    it('gets variables as pipe string', function () {
        $template = new SmsTemplate([
            'variables' => ['otp', 'validity'],
        ]);

        $pipeString = $template->getVariablesPipeString(['otp' => '123456', 'validity' => '10']);

        expect($pipeString)->toBe('123456|10');
    });

    it('records usage', function () {
        $integration = Integration::create([
            'name' => 'SMS Log Provider',
            'slug' => 'sms-log',
            'type' => IntegrationTypeCast::SMS,
            'credentials' => [],
            'settings' => [
                'driver' => 'log',
            ],
            'is_sandbox' => false,
            'is_active' => true,
            'is_default' => true,
        ]);

        $template = SmsTemplate::create([
            'integration_id' => $integration->id,
            'name' => 'Test',
            'slug' => 'test',
            'message_id' => '123456',
            'sender_id' => 'TEST',
            'content' => 'Test',
            'usage_count' => 0,
            'is_active' => true,
        ]);

        expect($template->usage_count)->toBe(0);

        $template->recordUsage();
        $template->refresh();

        expect($template->usage_count)->toBe(1)
            ->and($template->last_used_at)->not->toBeNull();
    });
});

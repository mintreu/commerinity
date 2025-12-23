<?php

declare(strict_types=1);

use App\Models\Integration;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Skip all webhook tests until webhooks are configured in provider dashboard
    // Remove this skip() call once webhooks are set up
    $this->markTestSkipped('Webhook tests skipped - waiting for webhook setup in provider dashboard');

    // Create test integration
    $this->webhookSecret = 'test_webhook_secret_123';

    $this->integration = Integration::create([
        'name' => 'Cashfree Test',
        'slug' => 'cashfree',
        'type' => Integration::TYPE_PAYMENT,
        'credentials' => [
            'app_id' => 'test_app_id',
            'secret_key' => 'test_secret_key',
            'webhook_secret' => $this->webhookSecret,
        ],
        'is_sandbox' => true,
        'is_active' => true,
        'is_default' => true,
    ]);

    // Create user and wallet
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->forUser($this->user)->create(['balance' => 0]);

    // Create pending transaction
    $this->transaction = Transaction::create([
        'uuid' => 'TXN-WEBHOOK-123',
        'wallet_id' => $this->wallet->id,
        'type' => 'credit',
        'amount' => 25000,
        'status' => 'pending',
        'payment_method' => 'cashfree',
    ]);
});

describe('Cashfree Payment Webhook', function () {
    it('handles payment success webhook', function () {
        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id' => 'TXN-WEBHOOK-123',
                    'order_amount' => 250.00,
                    'order_status' => 'PAID',
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_123456',
                    'payment_status' => 'SUCCESS',
                    'payment_method' => 'upi',
                ],
            ],
        ];

        $timestamp = (string) time();
        $rawBody = json_encode($payload);
        $signature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $this->webhookSecret, true)
        );

        $response = $this->postJson('/api/webhooks/cashfree', $payload, [
            'x-webhook-timestamp' => $timestamp,
            'x-webhook-signature' => $signature,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);

        // Verify transaction was updated
        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('completed')
            ->and($this->transaction->provider_reference)->toBe('cf_pay_123456');
    });

    it('handles payment failed webhook', function () {
        $payload = [
            'type' => 'PAYMENT_FAILED_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id' => 'TXN-WEBHOOK-123',
                    'order_status' => 'EXPIRED',
                ],
                'payment' => [
                    'payment_status' => 'FAILED',
                ],
            ],
        ];

        $timestamp = (string) time();
        $rawBody = json_encode($payload);
        $signature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $this->webhookSecret, true)
        );

        $response = $this->postJson('/api/webhooks/cashfree', $payload, [
            'x-webhook-timestamp' => $timestamp,
            'x-webhook-signature' => $signature,
        ]);

        $response->assertOk();

        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('failed');
    });

    it('handles refund webhook', function () {
        // First mark transaction as completed
        $this->transaction->update(['status' => 'completed']);

        $payload = [
            'type' => 'REFUND_STATUS_WEBHOOK',
            'data' => [
                'refund' => [
                    'order_id' => 'TXN-WEBHOOK-123',
                    'cf_refund_id' => 'cf_refund_789',
                    'refund_status' => 'SUCCESS',
                    'refund_amount' => 250.00,
                ],
            ],
        ];

        $timestamp = (string) time();
        $rawBody = json_encode($payload);
        $signature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $this->webhookSecret, true)
        );

        $response = $this->postJson('/api/webhooks/cashfree', $payload, [
            'x-webhook-timestamp' => $timestamp,
            'x-webhook-signature' => $signature,
        ]);

        $response->assertOk();

        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('refunded');
    });

    it('rejects webhook with invalid signature', function () {
        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => ['order_id' => 'TXN-WEBHOOK-123'],
            ],
        ];

        $response = $this->postJson('/api/webhooks/cashfree', $payload, [
            'x-webhook-timestamp' => (string) time(),
            'x-webhook-signature' => 'invalid_signature',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid signature']);

        // Transaction should remain unchanged
        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('pending');
    });

    it('handles missing timestamp header', function () {
        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [],
        ];

        $response = $this->postJson('/api/webhooks/cashfree', $payload, [
            'x-webhook-signature' => 'some_signature',
        ]);

        $response->assertStatus(401);
    });

    it('does not reprocess already completed transaction', function () {
        $this->transaction->update([
            'status' => 'completed',
            'provider_reference' => 'existing_ref',
        ]);

        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => ['order_id' => 'TXN-WEBHOOK-123'],
                'payment' => ['cf_payment_id' => 'new_payment_id'],
            ],
        ];

        $timestamp = (string) time();
        $rawBody = json_encode($payload);
        $signature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $this->webhookSecret, true)
        );

        $response = $this->postJson('/api/webhooks/cashfree', $payload, [
            'x-webhook-timestamp' => $timestamp,
            'x-webhook-signature' => $signature,
        ]);

        $response->assertOk();

        // Provider reference should not change
        $this->transaction->refresh();
        expect($this->transaction->provider_reference)->toBe('existing_ref');
    });
});

describe('Cashfree Payout Webhook', function () {
    beforeEach(function () {
        // Create payout integration
        Integration::create([
            'name' => 'Cashfree Payout Test',
            'slug' => 'cashfree',
            'type' => Integration::TYPE_PAYOUT,
            'credentials' => [
                'app_id' => 'test_payout_app_id',
                'secret_key' => 'test_payout_secret_key',
                'webhook_secret' => $this->webhookSecret,
            ],
            'is_sandbox' => true,
            'is_active' => true,
        ]);

        // Create withdrawal transaction
        $this->payoutTransaction = Transaction::create([
            'uuid' => 'PAYOUT-123',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'amount' => 150000,
            'status' => 'processing',
            'payment_method' => 'cashfree',
        ]);
    });

    it('handles payout success webhook', function () {
        $payload = [
            'event' => 'TRANSFER_SUCCESS',
            'transferId' => 'PAYOUT-123',
            'referenceId' => 'cf_transfer_ref_123',
            'utr' => 'UTR123456789',
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/cashfree/payout', $payload, [
            'x-webhook-signature' => $signature,
        ]);

        $response->assertOk();

        $this->payoutTransaction->refresh();
        expect($this->payoutTransaction->status->value)->toBe('completed')
            ->and($this->payoutTransaction->metadata['utr'])->toBe('UTR123456789');
    });

    it('handles payout failed webhook', function () {
        $payload = [
            'event' => 'TRANSFER_FAILED',
            'transferId' => 'PAYOUT-123',
            'reason' => 'Invalid beneficiary account',
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/cashfree/payout', $payload, [
            'x-webhook-signature' => $signature,
        ]);

        $response->assertOk();

        $this->payoutTransaction->refresh();
        expect($this->payoutTransaction->status->value)->toBe('failed');
    });
});

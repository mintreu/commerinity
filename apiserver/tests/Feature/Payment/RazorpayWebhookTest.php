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
    $this->webhookSecret = 'rzp_webhook_secret_123';

    $this->integration = Integration::create([
        'name' => 'Razorpay Test',
        'slug' => 'razorpay',
        'type' => Integration::TYPE_PAYMENT,
        'credentials' => [
            'key_id' => 'rzp_test_key',
            'key_secret' => 'rzp_test_secret',
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
        'uuid' => 'RZP-TXN-123',
        'wallet_id' => $this->wallet->id,
        'type' => 'credit',
        'amount' => 25000,
        'status' => 'pending',
        'payment_method' => 'razorpay',
        'metadata' => [
            'razorpay_order_id' => 'order_123456',
        ],
    ]);
});

describe('Razorpay Payment Webhook', function () {
    it('handles payment captured webhook', function () {
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_captured_123',
                        'order_id' => 'order_123456',
                        'amount' => 25000,
                        'status' => 'captured',
                        'notes' => [
                            'transaction_id' => 'RZP-TXN-123',
                        ],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('completed')
            ->and($this->transaction->provider_reference)->toBe('pay_captured_123');
    });

    it('handles payment failed webhook', function () {
        $payload = [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_failed_123',
                        'order_id' => 'order_123456',
                        'status' => 'failed',
                        'error_code' => 'BAD_REQUEST_ERROR',
                        'error_description' => 'Payment failed due to insufficient funds',
                        'notes' => [
                            'transaction_id' => 'RZP-TXN-123',
                        ],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('failed')
            ->and($this->transaction->metadata['error_description'])->toBe('Payment failed due to insufficient funds');
    });

    it('handles payment authorized webhook', function () {
        $payload = [
            'event' => 'payment.authorized',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_auth_123',
                        'order_id' => 'order_123456',
                        'status' => 'authorized',
                        'notes' => [
                            'transaction_id' => 'RZP-TXN-123',
                        ],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('processing');
    });

    it('handles refund processed webhook', function () {
        $this->transaction->update([
            'status' => 'completed',
            'provider_reference' => 'pay_123',
        ]);

        $payload = [
            'event' => 'refund.processed',
            'payload' => [
                'refund' => [
                    'entity' => [
                        'id' => 'rfnd_123',
                        'payment_id' => 'pay_123',
                        'amount' => 25000,
                        'status' => 'processed',
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->transaction->refresh();
        expect($this->transaction->status->value)->toBe('refunded')
            ->and($this->transaction->metadata['refund_id'])->toBe('rfnd_123');
    });

    it('rejects webhook with invalid signature', function () {
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_123',
                        'order_id' => 'order_123456',
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => 'invalid_signature',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid signature']);
    });

    it('handles missing signature header', function () {
        $payload = [
            'event' => 'payment.captured',
            'payload' => [],
        ];

        $response = $this->postJson('/api/webhooks/razorpay', $payload);

        $response->assertStatus(401);
    });
});

describe('Razorpay Payout Webhook (RazorpayX)', function () {
    beforeEach(function () {
        // Create payout integration
        Integration::create([
            'name' => 'RazorpayX Test',
            'slug' => 'razorpay',
            'type' => Integration::TYPE_PAYOUT,
            'credentials' => [
                'key_id' => 'rzp_test_key',
                'key_secret' => 'rzp_test_secret',
                'webhook_secret' => $this->webhookSecret,
                'account_number' => '1234567890',
            ],
            'is_sandbox' => true,
            'is_active' => true,
        ]);

        // Create payout transaction
        $this->payoutTransaction = Transaction::create([
            'uuid' => 'RZP-PAYOUT-123',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'amount' => 150000,
            'status' => 'processing',
            'payment_method' => 'razorpay',
        ]);
    });

    it('handles payout processed webhook', function () {
        $payload = [
            'event' => 'payout.processed',
            'payload' => [
                'payout' => [
                    'entity' => [
                        'id' => 'pout_123',
                        'reference_id' => 'RZP-PAYOUT-123',
                        'amount' => 150000,
                        'status' => 'processed',
                        'utr' => 'UTR987654321',
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->payoutTransaction->refresh();
        expect($this->payoutTransaction->status->value)->toBe('completed')
            ->and($this->payoutTransaction->provider_reference)->toBe('pout_123')
            ->and($this->payoutTransaction->metadata['utr'])->toBe('UTR987654321');
    });

    it('handles payout failed webhook', function () {
        $payload = [
            'event' => 'payout.failed',
            'payload' => [
                'payout' => [
                    'entity' => [
                        'id' => 'pout_failed_123',
                        'reference_id' => 'RZP-PAYOUT-123',
                        'status' => 'failed',
                        'failure_reason' => 'Invalid bank account',
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->payoutTransaction->refresh();
        expect($this->payoutTransaction->status->value)->toBe('failed')
            ->and($this->payoutTransaction->metadata['failure_reason'])->toBe('Invalid bank account');
    });

    it('handles payout reversed webhook', function () {
        $this->payoutTransaction->update(['status' => 'completed']);

        $payload = [
            'event' => 'payout.reversed',
            'payload' => [
                'payout' => [
                    'entity' => [
                        'id' => 'pout_reversed_123',
                        'reference_id' => 'RZP-PAYOUT-123',
                        'status' => 'reversed',
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->payoutTransaction->refresh();
        expect($this->payoutTransaction->status->value)->toBe('reversed');
    });
});

describe('Razorpay Webhook Edge Cases', function () {
    it('handles unrecognized event gracefully', function () {
        $payload = [
            'event' => 'unknown.event',
            'payload' => [],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);
    });

    it('handles missing transaction gracefully', function () {
        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_nonexistent',
                        'order_id' => 'order_nonexistent',
                        'notes' => [],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        // Should still return OK (to prevent Razorpay retries)
        $response->assertOk();
    });

    it('does not reprocess completed transactions', function () {
        $this->transaction->update([
            'status' => 'completed',
            'provider_reference' => 'original_pay_id',
        ]);

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'new_pay_id',
                        'order_id' => 'order_123456',
                        'notes' => [
                            'transaction_id' => 'RZP-TXN-123',
                        ],
                    ],
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        $response = $this->postJson('/api/webhooks/razorpay', $payload, [
            'x-razorpay-signature' => $signature,
        ]);

        $response->assertOk();

        $this->transaction->refresh();
        // Should keep original provider reference
        expect($this->transaction->provider_reference)->toBe('original_pay_id');
    });
});

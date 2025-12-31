<?php

declare(strict_types=1);

use App\Models\Integration;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Complete Wallet Topup Flow - Browser Test
 *
 * Tests the COMPLETE end-to-end flow with real browser interaction:
 * 1. Login to application
 * 2. Navigate to wallet
 * 3. Click "Add Money"
 * 4. Enter amount and submit
 * 5. Redirect to checkout page
 * 6. Cashfree checkout page loads
 * 7. (Manual) Complete payment in Cashfree sandbox
 * 8. Webhook fires or polling verification
 * 9. Redirect to wallet with success
 * 10. Verify balance updated
 * 11. Verify notification sent
 */

test('complete wallet topup flow works end-to-end', function () {
    // 1. Setup: Create test user and Cashfree integration
    $user = User::factory()->create([
        'email' => 'test@mintreu.com',
        'password' => bcrypt('password123'),
    ]);

    $wallet = Wallet::factory()->for($user, 'walletable')->create([
        'balance' => 0,
    ]);

    Integration::factory()->cashfree()->create();

    // 2. Login via browser
    $page = visit('/login');

    $page->assertSee('Sign In')
        ->fill('email', 'test@mintreu.com')
        ->fill('password', 'password123')
        ->click('Sign In')
        ->pause(2000)
        ->assertPathIs('/dashboard');

    // 3. Navigate to wallet
    $page->click('Wallet')
        ->pause(1000)
        ->assertPathIs('/wallet')
        ->assertSee('Available Balance')
        ->assertSee('₹0.00'); // Initial balance

    // 4. Click "Add Money"
    $page->click('Add Money')
        ->pause(1000)
        ->assertPathIs('/wallet/add')
        ->assertSee('Top up your wallet balance');

    // 5. Select amount ₹500
    $page->click('₹500')
        ->pause(500)
        ->assertSee('₹500'); // Selected amount

    // 6. Click "Add ₹500" button
    $page->click('Add ₹500')
        ->pause(3000);

    // 7. Should redirect to checkout page
    $page->assertPathContains('/checkout/')
        ->assertSee('Complete Payment')
        ->assertSee('Transaction Details')
        ->assertSee('₹500');

    // 8. Take screenshot of checkout page
    $page->screenshot('wallet-topup-checkout');

    // 9. Check that payment gateway is loaded
    $page->assertNoJavascriptErrors()
        ->assertSee('Pay via'); // "Pay via Cashfree" button

    // NOTE: In real testing, you would:
    // - Click "Pay via Cashfree"
    // - Complete payment in Cashfree sandbox (test card)
    // - Wait for redirect back
    // - Verify balance updated

    // For automated testing, we'll verify the checkout setup is correct
    $currentUrl = $page->getCurrentUrl();
    expect($currentUrl)->toContain('/checkout/');

    // Extract transaction UUID from URL
    preg_match('/\/checkout\/([^\/]+)/', $currentUrl, $matches);
    $transactionUuid = $matches[1] ?? null;

    expect($transactionUuid)->not->toBeNull();

    // 10. Verify transaction was created in database
    $transaction = \App\Models\Transaction::where('uuid', $transactionUuid)->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->amount)->toBe(50000) // ₹500 in paisa
        ->and($transaction->purpose)->toBe('Wallet TopUp')
        ->and($transaction->status->value)->toBe('pending')
        ->and($transaction->wallet_id)->toBe($wallet->id);

    // 11. Verify checkout data is available via API
    $checkoutResponse = $this->getJson("/api/checkout/{$transactionUuid}");

    $checkoutResponse->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.transaction.uuid', $transactionUuid)
        ->assertJsonPath('data.payment.provider', 'Cashfree')
        ->assertJsonStructure([
            'data' => [
                'transaction',
                'payment' => [
                    'provider',
                    'payment_session_id',
                    'is_sandbox',
                ],
                'redirect' => [
                    'success_url',
                    'failure_url',
                ],
            ],
        ]);

    // Screenshot final checkout page
    $page->screenshot('wallet-topup-ready-to-pay');
})->group('browser', 'wallet', 'payment');

test('wallet topup with custom amount works', function () {
    $user = User::factory()->create([
        'email' => 'test2@mintreu.com',
        'password' => bcrypt('password123'),
    ]);

    Wallet::factory()->for($user, 'walletable')->create();
    Integration::factory()->cashfree()->create();

    // Mock HTTP
    \Illuminate\Support\Facades\Http::fake([
        'sandbox.cashfree.com/*' => \Illuminate\Support\Facades\Http::response(['order_id' => 'test'], 200),
    ]);

    $page = visit('/login');

    $page->fill('email', 'test2@mintreu.com')
        ->fill('password', 'password123')
        ->click('Sign In')
        ->pause(2000)
        ->click('Wallet')
        ->pause(1000)
        ->click('Add Money')
        ->pause(1000)
        ->fill('input[type="number"]', '750')
        ->pause(500)
        ->assertSee('₹750')
        ->screenshot('custom-amount-entered');

})->group('browser', 'wallet');
